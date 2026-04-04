<?php

namespace App\Http\Controllers\Admin\Novel;

use App\Http\Controllers\Controller;
use App\Models\NovelWritingGuideline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NovelWritingGuidelineController extends Controller
{
    public function index(): View
    {
        $guidelines = NovelWritingGuideline::with('creator')->orderByDesc('created_at')->get();

        return view('admin.novel.guidelines.index', compact('guidelines'));
    }

    public function create(): View
    {
        return view('admin.novel.guidelines.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'genre' => ['required', 'string'],
            'is_active' => ['boolean'],
            'narrative_pov' => ['required', 'string'],
            'target_chapter_word_count' => ['required', 'integer', 'min:500', 'max:10000'],
            'language_style' => ['nullable', 'string'],
            'system_prompt_prefix' => ['nullable', 'string'],
            'plot_structure_notes' => ['nullable', 'string'],
            'forbidden_content' => ['nullable', 'string'],
            'content_guidelines' => ['nullable', 'string'],
            'character_archetypes' => ['nullable', 'string'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['created_by'] = session('admin_user.id');

        if (isset($data['character_archetypes'])) {
            $decoded = json_decode($data['character_archetypes'], true);
            $data['character_archetypes'] = $decoded ?? null;
        }

        NovelWritingGuideline::create($data);

        return redirect()->route('admin.novel.guidelines.index')
            ->with('success', 'Panduan penulisan berhasil dibuat.');
    }

    public function edit(NovelWritingGuideline $guideline): View
    {
        return view('admin.novel.guidelines.edit', compact('guideline'));
    }

    public function update(Request $request, NovelWritingGuideline $guideline): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'genre' => ['required', 'string'],
            'is_active' => ['boolean'],
            'narrative_pov' => ['required', 'string'],
            'target_chapter_word_count' => ['required', 'integer', 'min:500', 'max:10000'],
            'language_style' => ['nullable', 'string'],
            'system_prompt_prefix' => ['nullable', 'string'],
            'plot_structure_notes' => ['nullable', 'string'],
            'forbidden_content' => ['nullable', 'string'],
            'content_guidelines' => ['nullable', 'string'],
            'character_archetypes' => ['nullable', 'string'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if (isset($data['character_archetypes'])) {
            $decoded = json_decode($data['character_archetypes'], true);
            $data['character_archetypes'] = $decoded ?? null;
        }

        $guideline->update($data);

        return redirect()->route('admin.novel.guidelines.index')
            ->with('success', 'Panduan diperbarui.');
    }

    public function destroy(NovelWritingGuideline $guideline): RedirectResponse
    {
        $guideline->delete();

        return redirect()->route('admin.novel.guidelines.index')
            ->with('success', 'Panduan dihapus.');
    }
}
