<?php

namespace App\Http\Controllers\Admin\Novel;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateChapterContentJob;
use App\Jobs\GenerateNovelOutlinesJob;
use App\Jobs\GenerateNovelOverviewJob;
use App\Models\NovelChapter;
use App\Models\NovelStory;
use App\Models\NovelWritingGuideline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NovelStoryController extends Controller
{
    public function index(): View
    {
        $stories = NovelStory::with('creator')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.novel.stories.index', compact('stories'));
    }

    public function create(): View
    {
        $guidelines = NovelWritingGuideline::where('is_active', true)->get();

        return view('admin.novel.stories.create', compact('guidelines'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'genre' => ['required', 'string'],
            'total_chapters_planned' => ['required', 'integer', 'min:5', 'max:200'],
            'novel_writing_guideline_id' => ['nullable', 'exists:novel_writing_guidelines,id'],
            'overview_prompt_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $adminId = session('admin_user.id');

        $story = NovelStory::create([
            'genre' => $request->genre,
            'total_chapters_planned' => $request->total_chapters_planned,
            'novel_writing_guideline_id' => $request->novel_writing_guideline_id,
            'overview_prompt_notes' => $request->overview_prompt_notes,
            'status' => 'draft',
            'created_by' => $adminId,
        ]);

        GenerateNovelOverviewJob::dispatch($story->id, $adminId);

        return redirect()->route('admin.novel.stories.show', $story)
            ->with('success', 'Novel dibuat! AI sedang generate ringkasan...');
    }

    public function show(NovelStory $story): View
    {
        $story->load(['guideline', 'chapters', 'creator', 'aiUsages']);

        $analyticsData = [];
        if ($story->total_input_tokens > 0) {
            $usages = $story->aiUsages;

            // Cost per stage
            $costPerStage = [
                'overview' => 0,
                'outline' => 0,
                'content' => 0,
            ];
            foreach ($usages as $u) {
                if (isset($costPerStage[$u->stage])) {
                    $costPerStage[$u->stage] += $u->estimated_cost_usd;
                }
            }

            // Token per stage
            $tokensPerStage = [
                'overview' => ['in' => 0, 'out' => 0],
                'outline' => ['in' => 0, 'out' => 0],
                'content' => ['in' => 0, 'out' => 0],
            ];
            foreach ($usages as $u) {
                if (isset($tokensPerStage[$u->stage])) {
                    $tokensPerStage[$u->stage]['in'] += $u->input_tokens;
                    $tokensPerStage[$u->stage]['out'] += $u->output_tokens;
                }
            }

            // Cost per chapter
            $costPerChapter = [];
            foreach ($story->chapters as $ch) {
                $chapterUsages = $usages->where('novel_chapter_id', $ch->id);
                $costPerChapter[$ch->chapter_number] = [
                    'id' => $ch->id,
                    'title' => $ch->title ?? 'Bab '.$ch->chapter_number,
                    'status' => $ch->content_status,
                    'cost' => $chapterUsages->sum('estimated_cost_usd'),
                    'generation_count' => $ch->content_generation_count,
                ];
            }

            $totalCost = array_sum($costPerStage);
            $approvedCount = $story->chapters->where('content_status', 'approved')->count();

            $analyticsData = [
                'total_cost' => $totalCost,
                'cost_per_stage' => $costPerStage,
                'tokens_per_stage' => $tokensPerStage,
                'cost_per_chapter' => $costPerChapter,
                'avg_cost_per_chapter' => $approvedCount > 0 ? $totalCost / $approvedCount : 0,
                'usages' => $usages->sortBy('created_at')->values(),
            ];
        }

        return view('admin.novel.stories.show', compact('story', 'analyticsData'));
    }

    public function status(NovelStory $story): JsonResponse
    {
        $outlineProgress = null;
        if (in_array($story->status, ['outline_pending', 'outline_ready', 'outline_approved'])) {
            $outlineProgress = [
                'done' => $story->chapters()->where('outline_status', 'approved')->count(),
                'failed' => $story->chapters()->where('outline_status', 'failed')->count(),
                'total' => $story->total_chapters_planned,
            ];
        }

        return response()->json([
            'status' => $story->status,
            'status_label' => $story->statusLabel(),
            'title_draft' => $story->title_draft,
            'outline_progress' => $outlineProgress,
        ]);
    }

    public function approveOverview(Request $request, NovelStory $story): RedirectResponse
    {
        if (! $story->canApproveOverview()) {
            return back()->with('error', 'Ringkasan tidak dalam status siap untuk diapprove.');
        }

        $story->update([
            'status' => 'overview_approved',
            'title' => $story->title_draft,
            'approved_overview_at' => now(),
            'approved_overview_by' => session('admin_user.id'),
        ]);

        return back()->with('success', 'Ringkasan disetujui! Kini bisa generate outline bab.');
    }

    public function rejectOverview(Request $request, NovelStory $story): RedirectResponse
    {
        if (! $story->canApproveOverview()) {
            return back()->with('error', 'Ringkasan tidak dalam status siap untuk ditolak.');
        }

        $story->update([
            'status' => 'draft',
            'overview_prompt_notes' => $request->input('rejection_notes', $story->overview_prompt_notes),
        ]);

        return back()->with('success', 'Ringkasan ditolak. Edit catatan dan generate ulang.');
    }

    public function regenerateOverview(NovelStory $story): RedirectResponse
    {
        if (! in_array($story->status, ['draft', 'overview_ready', 'overview_approved'])) {
            return back()->with('error', 'Tidak bisa regenerate pada status ini.');
        }

        $adminId = session('admin_user.id');
        $story->update(['status' => 'overview_pending']);
        GenerateNovelOverviewJob::dispatch($story->id, $adminId);

        return back()->with('success', 'Ringkasan di-generate ulang...');
    }

    public function dispatchOutlines(NovelStory $story): RedirectResponse
    {
        if (! $story->canGenerateOutlines()) {
            return back()->with('error', 'Ringkasan belum diapprove.');
        }

        $adminId = session('admin_user.id');
        $story->update(['status' => 'outline_pending']);
        GenerateNovelOutlinesJob::dispatch($story->id, $adminId);

        return back()->with('success', 'AI sedang generate outline semua bab...');
    }

    public function approveAllOutlines(NovelStory $story): RedirectResponse
    {
        if (! $story->canApproveOutlines()) {
            return back()->with('error', 'Outline tidak dalam status siap untuk diapprove.');
        }

        $adminId = session('admin_user.id');

        $story->chapters()->where('outline_status', 'ready')->update(['outline_status' => 'approved']);

        $story->update([
            'status' => 'outline_approved',
            'approved_outline_at' => now(),
            'approved_outline_by' => $adminId,
        ]);

        return back()->with('success', 'Semua outline disetujui! Kini bisa generate konten per bab.');
    }

    public function updateOverview(Request $request, NovelStory $story): RedirectResponse
    {
        if (! in_array($story->status, ['overview_ready', 'overview_approved'])) {
            return back()->with('error', 'Ringkasan tidak bisa diedit pada status ini.');
        }

        $request->validate([
            'title_draft' => ['required', 'string', 'max:500'],
            'theme' => ['nullable', 'string', 'max:1000'],
            'synopsis' => ['nullable', 'string'],
            'general_overview' => ['nullable', 'string'],
            'characters' => ['nullable', 'string'],
            'plot_points' => ['nullable', 'string'],
        ]);

        $characters = $story->characters;
        if ($request->filled('characters')) {
            $decoded = json_decode($request->characters, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $characters = $decoded;
            }
        }

        $plotPoints = $story->plot_points;
        if ($request->filled('plot_points')) {
            $decoded = json_decode($request->plot_points, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $plotPoints = $decoded;
            }
        }

        $story->update([
            'title_draft' => $request->title_draft,
            'theme' => $request->theme,
            'synopsis' => $request->synopsis,
            'general_overview' => $request->general_overview,
            'characters' => $characters,
            'plot_points' => $plotPoints,
        ]);

        return back()->with('success', 'Ringkasan diperbarui.');
    }

    public function generateBulkContent(Request $request, NovelStory $story): RedirectResponse
    {
        if (! $story->isContentPhase()) {
            return back()->with('error', 'Novel belum di tahap konten.');
        }

        $adminId = session('admin_user.id');
        $chapterIds = $request->input('chapter_ids', []);

        if (empty($chapterIds) || in_array('all', $chapterIds)) {
            $chapters = $story->chapters()
                ->whereIn('content_status', ['pending', 'failed', 'revision_requested'])
                ->whereIn('outline_status', ['ready', 'approved'])
                ->get();
        } else {
            $chapters = NovelChapter::whereIn('id', $chapterIds)
                ->where('novel_story_id', $story->id)
                ->whereIn('outline_status', ['ready', 'approved'])
                ->whereIn('content_status', ['pending', 'failed', 'revision_requested'])
                ->get();
        }

        if ($chapters->isEmpty()) {
            return back()->with('error', 'Tidak ada bab yang bisa di-generate (outline belum approved atau sudah selesai).');
        }

        $i = 0;
        foreach ($chapters as $chapter) {
            $chapter->update(['content_status' => 'generating']);
            // Stagger 20s each: content prompt ~10,000 tokens → max 3 safe jobs/min
            GenerateChapterContentJob::dispatch($chapter->id, $adminId)
                ->delay(now()->addSeconds($i * 20));
            $i++;
        }

        return back()->with('success', "Generate konten dimulai untuk {$chapters->count()} bab.");
    }

    public function destroy(NovelStory $story): RedirectResponse
    {
        $story->delete();

        return redirect()->route('admin.novel.stories.index')
            ->with('success', 'Novel dihapus.');
    }
}
