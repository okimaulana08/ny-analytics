<?php

namespace App\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class ContentRecommender
{
    private string $novelyaUrl;

    public function __construct()
    {
        $this->novelyaUrl = config('brevo.novelya_url', 'https://novelya.id');
    }

    /**
     * Get the top recommended story params for a given user (single story, backwards compat).
     *
     * @return array{story_title: string, story_cover: string, story_synopsis: string, story_url: string}|null
     */
    public function getTopForUser(?string $userId): ?array
    {
        return $this->getTopNForUser($userId, 1)[0] ?? null;
    }

    /**
     * Get the top N recommended stories for a given user.
     * Uses category-affinity scoring when history is available, falls back to freshness+popularity.
     *
     * @return array<int, array{story_title: string, story_cover: string, story_synopsis: string, story_url: string, story_category: string}>
     */
    public function getTopNForUser(?string $userId, int $n = 3): array
    {
        $db = DB::connection('novel');

        if (! $userId) {
            return $this->coldStartN($db, null, $n);
        }

        $topCategories = $db->select('
            SELECT c.category_id AS id, COUNT(*) AS read_count
            FROM read_history rh
            JOIN content c ON c.id = rh.content_id AND c.is_deleted = 0
            WHERE rh.user_id = ? AND rh.is_deleted = 0 AND c.category_id IS NOT NULL
            GROUP BY c.category_id
            ORDER BY read_count DESC
            LIMIT 4
        ', [$userId]);

        if (empty($topCategories)) {
            return $this->coldStartN($db, $userId, $n);
        }

        $weights = [40, 30, 20, 10];
        $caseWhen = '';
        $catParams = [];

        foreach ($topCategories as $i => $cat) {
            $w = $weights[$i] ?? 5;
            $caseWhen .= " WHEN c.category_id = ? THEN {$w}";
            $catParams[] = $cat->id;
        }

        $rows = $db->select("
            SELECT c.title, c.slug, c.synopsis, c.cover_image,
                   mcc.name AS category_name
            FROM content c
            LEFT JOIN master_content_category mcc ON mcc.id = c.category_id
            WHERE c.is_published = 1 AND c.is_deleted = 0
              AND c.id NOT IN (
                  SELECT DISTINCT content_id FROM read_history
                  WHERE user_id = ? AND is_deleted = 0
              )
            ORDER BY (
                CASE {$caseWhen} ELSE 0 END
                + GREATEST(0, 25 - FLOOR(DATEDIFF(NOW(), COALESCE(c.published_at, c.created_at)) / 3.6))
                + LEAST(25, FLOOR(LOG(GREATEST(c.subscribe_count + 1, 1)) * 5))
                + LEAST(10, FLOOR(c.rating * 2))
            ) DESC
            LIMIT {$n}
        ", array_merge($catParams, [$userId]));

        return array_map(fn ($r) => $this->format($r), $rows);
    }

    private function coldStart(ConnectionInterface $db, ?string $userId): ?array
    {
        return $this->coldStartN($db, $userId, 1)[0] ?? null;
    }

    /**
     * @return array<int, array{story_title: string, story_cover: string, story_synopsis: string, story_url: string, story_category: string}>
     */
    private function coldStartN(ConnectionInterface $db, ?string $userId, int $n): array
    {
        $excludeClause = $userId
            ? 'AND c.id NOT IN (SELECT DISTINCT content_id FROM read_history WHERE user_id = ? AND is_deleted = 0)'
            : '';

        $rows = $db->select("
            SELECT c.title, c.slug, c.synopsis, c.cover_image,
                   mcc.name AS category_name
            FROM content c
            LEFT JOIN master_content_category mcc ON mcc.id = c.category_id
            WHERE c.is_published = 1 AND c.is_deleted = 0
              {$excludeClause}
            ORDER BY (
                GREATEST(0, 25 - FLOOR(DATEDIFF(NOW(), COALESCE(c.published_at, c.created_at)) / 3.6))
                + LEAST(25, FLOOR(LOG(GREATEST(c.subscribe_count + 1, 1)) * 5))
                + LEAST(10, FLOOR(c.rating * 2))
            ) DESC
            LIMIT {$n}
        ", $userId ? [$userId] : []);

        return array_map(fn ($r) => $this->format($r), $rows);
    }

    /**
     * @return array{story_title: string, story_cover: string, story_synopsis: string, story_url: string, story_category: string}
     */
    private function format(object $story): array
    {
        $synopsis = $story->synopsis ?? '';
        if (mb_strlen($synopsis) > 200) {
            $synopsis = mb_substr($synopsis, 0, 200).'...';
        }

        return [
            'story_title' => $story->title ?? '',
            'story_cover' => $story->cover_image ?? '',
            'story_synopsis' => $synopsis,
            'story_url' => $this->novelyaUrl.'/detail/'.($story->slug ?? ''),
            'story_category' => $story->category_name ?? '',
        ];
    }
}
