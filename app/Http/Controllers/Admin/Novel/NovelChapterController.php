<?php

namespace App\Http\Controllers\Admin\Novel;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateChapterContentJob;
use App\Jobs\GenerateSingleOutlineJob;
use App\Models\NovelChapter;
use App\Models\NovelStory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NovelChapterController extends Controller
{
    public function show(NovelStory $story, NovelChapter $chapter): View
    {
        $chapter->load(['story.guideline', 'story.chapters']);

        return view('admin.novel.chapters.show', compact('story', 'chapter'));
    }

    public function approveOutline(NovelStory $story, NovelChapter $chapter): RedirectResponse
    {
        if (! $chapter->canApproveOutline()) {
            return back()->with('error', 'Outline tidak dalam status siap untuk diapprove.');
        }

        $chapter->update([
            'outline_status' => 'approved',
            'approved_outline_at' => now(),
            'approved_outline_by' => session('admin_user.id'),
        ]);

        return back()->with('success', 'Outline disetujui.');
    }

    public function regenerateOutline(Request $request, NovelStory $story, NovelChapter $chapter): RedirectResponse
    {
        if (! in_array($chapter->outline_status, ['ready', 'failed', 'pending'])) {
            return back()->with('error', 'Tidak bisa regenerate outline pada status ini.');
        }

        if ($request->filled('outline_prompt_notes')) {
            $chapter->update(['outline_prompt_notes' => $request->outline_prompt_notes]);
        }

        $adminId = session('admin_user.id');
        $chapter->update(['outline_status' => 'generating']);

        // Use single outline job for regeneration
        GenerateSingleOutlineJob::dispatch($chapter->id, $adminId);

        return back()->with('success', 'Outline bab di-generate ulang...');
    }

    public function generateContent(Request $request, NovelStory $story, NovelChapter $chapter): RedirectResponse
    {
        if (! $chapter->canGenerateContent()) {
            return back()->with('error', 'Outline belum diapprove atau konten sedang diproses.');
        }

        if ($request->filled('content_prompt_notes')) {
            $chapter->update(['content_prompt_notes' => $request->content_prompt_notes]);
        }

        $adminId = session('admin_user.id');
        $chapter->update(['content_status' => 'generating']);
        GenerateChapterContentJob::dispatch($chapter->id, $adminId);

        return back()->with('success', 'AI sedang menulis konten bab...');
    }

    public function approveContent(NovelStory $story, NovelChapter $chapter): RedirectResponse
    {
        if (! $chapter->canApproveContent()) {
            return back()->with('error', 'Konten tidak dalam status siap untuk diapprove.');
        }

        $chapter->update([
            'content_status' => 'approved',
            'approved_content_at' => now(),
            'approved_content_by' => session('admin_user.id'),
        ]);

        // Check if all chapters approved → mark story complete
        $story->refresh();
        if ($story->allChaptersContentApproved()) {
            $story->update(['status' => 'content_complete']);
        }

        return back()->with('success', 'Konten bab disetujui!');
    }

    public function updateOutline(Request $request, NovelStory $story, NovelChapter $chapter): RedirectResponse
    {
        if (! in_array($chapter->outline_status, ['ready', 'approved'])) {
            return back()->with('error', 'Outline tidak bisa diedit pada status ini.');
        }

        $request->validate([
            'title' => ['nullable', 'string', 'max:500'],
            'outline_content' => ['required', 'string'],
        ]);

        $chapter->update([
            'title' => $request->title,
            'outline_content' => $request->outline_content,
        ]);

        return back()->with('success', 'Outline diperbarui.');
    }

    public function updateContent(Request $request, NovelStory $story, NovelChapter $chapter): RedirectResponse
    {
        if (! in_array($chapter->content_status, ['ready', 'approved', 'revision_requested'])) {
            return back()->with('error', 'Konten tidak bisa diedit pada status ini.');
        }

        $request->validate([
            'title' => ['nullable', 'string', 'max:500'],
            'content_draft' => ['required', 'string'],
        ]);

        $chapter->update([
            'title' => $request->title,
            'content_draft' => $request->content_draft,
        ]);

        return back()->with('success', 'Konten diperbarui.');
    }

    public function requestRevision(Request $request, NovelStory $story, NovelChapter $chapter): RedirectResponse
    {
        if ($chapter->content_status !== 'ready') {
            return back()->with('error', 'Konten tidak dalam status siap untuk diminta revisi.');
        }

        $chapter->update([
            'content_status' => 'revision_requested',
            'content_revision_note' => $request->input('revision_note'),
        ]);

        return back()->with('success', 'Catatan revisi disimpan.');
    }
}
