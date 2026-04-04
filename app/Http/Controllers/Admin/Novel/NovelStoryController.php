<?php

namespace App\Http\Controllers\Admin\Novel;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateNovelOutlinesJob;
use App\Jobs\GenerateNovelOverviewJob;
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
        $story->load(['guideline', 'chapters', 'creator']);

        return view('admin.novel.stories.show', compact('story'));
    }

    public function status(NovelStory $story): JsonResponse
    {
        return response()->json([
            'status' => $story->status,
            'status_label' => $story->statusLabel(),
            'title_draft' => $story->title_draft,
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
        if (! in_array($story->status, ['draft', 'overview_ready'])) {
            return back()->with('error', 'Tidak bisa regenerate pada status ini.');
        }

        $adminId = session('admin_user.id');
        $story->update(['status' => 'draft']);
        GenerateNovelOverviewJob::dispatch($story->id, $adminId);

        return back()->with('success', 'Ringkasan di-generate ulang...');
    }

    public function dispatchOutlines(NovelStory $story): RedirectResponse
    {
        if (! $story->canGenerateOutlines()) {
            return back()->with('error', 'Ringkasan belum diapprove.');
        }

        $adminId = session('admin_user.id');
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

    public function destroy(NovelStory $story): RedirectResponse
    {
        $story->delete();

        return redirect()->route('admin.novel.stories.index')
            ->with('success', 'Novel dihapus.');
    }
}
