<?php

namespace App\Http\Controllers\Admin\Novel;

use App\Http\Controllers\Controller;
use App\Models\NovelChapter;
use App\Models\NovelStory;
use Illuminate\Http\Response;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class NovelExportController extends Controller
{
    public function storyPdf(NovelStory $story): Response
    {
        $story->load(['chapters' => function ($q) {
            $q->where('content_status', 'approved')->orderBy('chapter_number');
        }]);

        return response()
            ->view('admin.novel.export.story-pdf', compact('story'))
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function chapterPdf(NovelStory $story, NovelChapter $chapter): Response
    {
        return response()
            ->view('admin.novel.export.chapter-pdf', compact('story', 'chapter'))
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function storyDocx(NovelStory $story): Response
    {
        $story->load(['chapters' => function ($q) {
            $q->where('content_status', 'approved')->orderBy('chapter_number');
        }]);

        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 20, 'name' => 'Georgia'], ['alignment' => 'center', 'spaceAfter' => 240]);
        $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 14, 'name' => 'Georgia'], ['spaceBefore' => 480, 'spaceAfter' => 120]);

        // Cover section
        $cover = $phpWord->addSection(['marginTop' => 1440, 'marginBottom' => 1440]);
        $cover->addTitle($story->title_draft ?? $story->title ?? 'Untitled', 1);
        $cover->addTextBreak(2);
        $cover->addText($story->genreLabel(), ['size' => 11, 'color' => '888888'], ['alignment' => 'center']);
        if ($story->synopsis) {
            $cover->addTextBreak(2);
            $cover->addText('Sinopsis', ['bold' => true, 'size' => 10, 'color' => '888888', 'name' => 'Arial'], ['alignment' => 'center']);
            $cover->addTextBreak(1);
            foreach (explode("\n", $story->synopsis) as $para) {
                if (trim($para)) {
                    $cover->addText(trim($para), ['size' => 11], ['alignment' => 'both', 'lineHeight' => 1.8]);
                }
            }
        }

        // Chapters
        foreach ($story->chapters as $chapter) {
            $section = $phpWord->addSection([
                'marginTop' => 1440,
                'marginBottom' => 1440,
                'marginLeft' => 1080,
                'marginRight' => 1080,
                'breakType' => 'nextPage',
            ]);

            $section->addText('Bab '.$chapter->chapter_number, ['size' => 9, 'color' => 'aaaaaa', 'name' => 'Arial']);
            $section->addTitle($chapter->title ?: 'Bab '.$chapter->chapter_number, 2);
            $section->addTextBreak(1);

            $content = $chapter->content_draft ?? '';
            $paragraphs = array_filter(explode("\n", $content));
            foreach ($paragraphs as $para) {
                $para = trim($para);
                if ($para) {
                    $section->addText(
                        htmlspecialchars_decode(strip_tags($para)),
                        ['size' => 12, 'name' => 'Times New Roman'],
                        ['alignment' => 'both', 'lineHeight' => 1.9, 'spaceAfter' => 120]
                    );
                }
            }
        }

        $filename = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $story->title_draft ?? 'novel').'.docx';
        $temp = tempnam(sys_get_temp_dir(), 'novel_').'.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($temp);

        $content = file_get_contents($temp);
        @unlink($temp);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => strlen($content),
        ]);
    }
}
