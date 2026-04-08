<?php

namespace App\Http\Controllers\Admin\Novel;

use App\Exceptions\NovelyaPublishException;
use App\Http\Controllers\Controller;
use App\Models\NovelStory;
use App\Models\Novelya\NovelyaContentCategory;
use App\Models\Novelya\NovelyaUser;
use App\Services\NovelyaPublishService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NovelyaPublishController extends Controller
{
    public function __construct(private NovelyaPublishService $publishService) {}

    /**
     * Show the publish form (step 1).
     */
    public function showForm(NovelStory $story): View
    {
        $categories = NovelyaContentCategory::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $approvedChapters = $story->chapters()
            ->where('content_status', 'approved')
            ->orderBy('chapter_number')
            ->get(['id', 'chapter_number', 'title', 'content_draft']);

        // Pre-load previously selected author if any
        $selectedAuthor = $story->novelya_author_id
            ? NovelyaUser::find($story->novelya_author_id, ['id', 'name', 'email'])
            : null;

        return view('admin.novel.stories.publish', compact(
            'story', 'categories', 'approvedChapters', 'selectedAuthor'
        ));
    }

    /**
     * Search authors by name or email (AJAX, min 2 chars).
     */
    public function searchAuthors(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $authors = NovelyaUser::query()
            ->select('id', 'name', 'email')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get();

        return response()->json($authors);
    }

    /**
     * Upload cover image to DO Spaces (AJAX).
     */
    public function uploadCover(Request $request, NovelStory $story): JsonResponse
    {
        $request->validate([
            'cover' => 'required|image|mimes:jpeg,png,webp|max:2048',
        ]);

        $path = $this->publishService->uploadCoverToSpaces($request->file('cover'), $story->id);
        $url = $this->publishService->coverUrl($path);

        return response()->json(['path' => $path, 'url' => $url]);
    }

    /**
     * Validate and return preview data (AJAX).
     */
    public function preview(Request $request, NovelStory $story): JsonResponse
    {
        $validated = $request->validate([
            'author_id' => 'required|string',
            'category_id' => 'required|string',
            'cover_path' => 'required|string',
            'synopsis' => 'required|string|min:25|max:2048',
            'tags' => 'nullable|string|max:512',
            'is_published' => 'boolean',
        ]);

        $author = NovelyaUser::find($validated['author_id']);
        $category = NovelyaContentCategory::find($validated['category_id']);

        $chapters = $story->chapters()
            ->where('content_status', 'approved')
            ->orderBy('chapter_number')
            ->get(['chapter_number', 'title', 'content_draft'])
            ->map(fn ($ch) => [
                'number' => $ch->chapter_number,
                'title' => $ch->title ?? "Bab {$ch->chapter_number}",
                'word_count' => str_word_count($ch->content_draft ?? ''),
            ]);

        return response()->json([
            'title' => $story->title_draft ?? $story->title,
            'synopsis' => $validated['synopsis'],
            'author_name' => $author?->name ?? '—',
            'category_name' => $category?->name ?? '—',
            'cover_url' => $this->publishService->coverUrl($validated['cover_path']),
            'tags' => $validated['tags'] ?? '',
            'is_published' => (bool) ($validated['is_published'] ?? false),
            'chapters' => $chapters,
            'total_chapters' => $chapters->count(),
            'total_words' => $chapters->sum('word_count'),
        ]);
    }

    /**
     * Execute publish: create story + send chapters (AJAX).
     */
    public function execute(Request $request, NovelStory $story): JsonResponse
    {
        $validated = $request->validate([
            'author_id' => 'required|string',
            'category_id' => 'required|string',
            'cover_path' => 'required|string',
            'synopsis' => 'required|string|min:25|max:2048',
            'tags' => 'nullable|string|max:512',
            'is_published' => 'boolean',
        ]);

        $isPublished = (bool) ($validated['is_published'] ?? false);

        try {
            // Step 1: Create story on Novelya
            $novelyaStoryId = $this->publishService->createStory(
                story: $story,
                authorId: $validated['author_id'],
                categoryId: $validated['category_id'],
                coverSpacesPath: $validated['cover_path'],
                tags: $validated['tags'] ?? null,
                isPublished: $isPublished,
                synopsisOverride: $validated['synopsis'],
            );

            // Save story ID immediately (for retry if chapters fail)
            $story->update([
                'novelya_story_id' => $novelyaStoryId,
                'novelya_author_id' => $validated['author_id'],
                'novelya_category_id' => $validated['category_id'],
                'novelya_cover_path' => $validated['cover_path'],
                'novelya_chapters_published' => 0,
                'novelya_publish_error' => null,
            ]);

            // Step 2: Send chapters in batches
            $totalSent = $this->publishService->sendChapters($novelyaStoryId, $story, $isPublished);

            // All done — mark as published
            $story->update([
                'status' => 'published',
                'published_to_novelya_at' => now(),
                'novelya_publish_error' => null,
                'novelya_chapters_published' => $totalSent,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil publish ke Novelya! {$totalSent} chapter terkirim.",
                'chapters_sent' => $totalSent,
            ]);

        } catch (NovelyaPublishException $e) {
            return response()->json([
                'success' => false,
                'partial' => $e->storyCreated,
                'chapters_sent' => $e->chaptersSent,
                'total_chapters' => $story->chapters()->where('content_status', 'approved')->count(),
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Retry sending remaining chapters (AJAX).
     */
    public function retryChapters(Request $request, NovelStory $story): JsonResponse
    {
        $isPublished = (bool) ($request->input('is_published', false));

        try {
            $totalSent = $this->publishService->retryChapters($story, $isPublished);

            $totalApproved = $story->chapters()->where('content_status', 'approved')->count();

            if ($totalSent >= $totalApproved) {
                $story->update([
                    'status' => 'published',
                    'published_to_novelya_at' => now(),
                    'novelya_publish_error' => null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengirim chapter! {$totalSent}/{$totalApproved} chapter terkirim.",
                'chapters_sent' => $totalSent,
            ]);

        } catch (NovelyaPublishException $e) {
            return response()->json([
                'success' => false,
                'partial' => $e->storyCreated,
                'chapters_sent' => $e->chaptersSent,
                'total_chapters' => $story->chapters()->where('content_status', 'approved')->count(),
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
