<?php

namespace App\Services;

use App\Exceptions\NovelyaPublishException;
use App\Models\NovelStory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class NovelyaPublishService
{
    private string $apiKey;

    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('novelya.api_key');
        $this->baseUrl = config('novelya.api_base_url');
    }

    /**
     * Upload cover image to DigitalOcean Spaces.
     *
     * @return string The stored path
     */
    public function uploadCoverToSpaces(UploadedFile $file, int $storyId): string
    {
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = "novel-covers/{$storyId}-".time().".{$extension}";

        Storage::disk('do_spaces')->put($filename, file_get_contents($file->getRealPath()), 'public');

        return $filename;
    }

    /**
     * Get full public URL for a DO Spaces path.
     */
    public function coverUrl(string $path): string
    {
        return rtrim(config('filesystems.disks.do_spaces.url'), '/').'/'.$path;
    }

    /**
     * Create story on Novelya API via multipart form.
     *
     * @return string The Novelya story UUID
     *
     * @throws NovelyaPublishException
     */
    public function createStory(
        NovelStory $story,
        string $authorId,
        string $categoryId,
        string $coverSpacesPath,
        ?string $tags = null,
        bool $isPublished = false,
        ?string $synopsisOverride = null,
    ): string {
        $synopsis = $synopsisOverride ?? $story->synopsis;

        // Download cover from DO Spaces to send as multipart
        $coverContents = Storage::disk('do_spaces')->get($coverSpacesPath);
        $coverFilename = basename($coverSpacesPath);

        $request = Http::timeout(30)
            ->withHeaders(['X-Api-Key' => $this->apiKey])
            ->asMultipart()
            ->attach('cover_image', $coverContents, $coverFilename);

        $fields = [
            ['name' => 'author_id', 'contents' => $authorId],
            ['name' => 'title', 'contents' => $story->title_draft ?? $story->title],
            ['name' => 'synopsis', 'contents' => $synopsis],
            ['name' => 'category_id', 'contents' => $categoryId],
            ['name' => 'is_published', 'contents' => $isPublished ? '1' : '0'],
        ];

        if ($tags) {
            $fields[] = ['name' => 'tags', 'contents' => $tags];
        }

        $response = $request->post("{$this->baseUrl}/story", $fields);

        if (! $response->successful()) {
            $errorBody = $response->json('message', $response->body());
            throw new NovelyaPublishException("Gagal membuat cerita di Novelya: {$errorBody}");
        }

        $data = $response->json();

        return $data['data']['id'] ?? $data['id'] ?? $data['story']['id'] ?? '';
    }

    /**
     * Send approved chapters in batches of 100.
     *
     * @return int Total chapters successfully sent
     *
     * @throws NovelyaPublishException
     */
    public function sendChapters(string $novelyaStoryId, NovelStory $story, bool $isPublished = false): int
    {
        $chapters = $story->chapters()
            ->where('content_status', 'approved')
            ->orderBy('chapter_number')
            ->get();

        $offset = $story->novelya_chapters_published ?? 0;
        $remaining = $chapters->slice($offset);

        if ($remaining->isEmpty()) {
            return $offset;
        }

        $totalSent = $offset;

        foreach ($remaining->chunk(100) as $batch) {
            $payload = $batch->map(fn ($ch) => [
                'title' => $ch->title ?? "Bab {$ch->chapter_number}",
                'body' => $this->contentToHtml($ch->content_draft ?? ''),
                'sequence' => $ch->chapter_number,
                'is_published' => $isPublished,
            ])->values()->all();

            $response = Http::timeout(60)
                ->withHeaders([
                    'X-Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/story/{$novelyaStoryId}/chapter", [
                    'chapters' => $payload,
                ]);

            if (! $response->successful()) {
                $errorBody = $response->json('message', $response->body());

                // Save progress so far
                $story->update([
                    'novelya_chapters_published' => $totalSent,
                    'novelya_publish_error' => "Gagal mengirim batch chapter: {$errorBody}",
                ]);

                throw new NovelyaPublishException(
                    "Gagal mengirim chapter ke Novelya: {$errorBody}",
                    storyCreated: true,
                    chaptersSent: $totalSent,
                );
            }

            $totalSent += $batch->count();

            // Save progress after each successful batch
            $story->update(['novelya_chapters_published' => $totalSent]);
        }

        return $totalSent;
    }

    /**
     * Retry sending remaining chapters after partial failure.
     *
     * @return int Total chapters sent (cumulative)
     *
     * @throws NovelyaPublishException
     */
    public function retryChapters(NovelStory $story, bool $isPublished = false): int
    {
        if (! $story->novelya_story_id) {
            throw new NovelyaPublishException('Cerita belum dibuat di Novelya. Silakan publish ulang.');
        }

        return $this->sendChapters($story->novelya_story_id, $story, $isPublished);
    }

    /**
     * Convert plain text chapter content to simple HTML paragraphs.
     */
    private function contentToHtml(string $plainText): string
    {
        $lines = explode("\n", $plainText);
        $paragraphs = array_filter(
            array_map('trim', $lines),
            fn ($line) => $line !== ''
        );

        return implode("\n", array_map(
            fn ($p) => '<p>'.htmlspecialchars($p, ENT_QUOTES, 'UTF-8').'</p>',
            $paragraphs
        ));
    }
}
