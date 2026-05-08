<?php

declare(strict_types=1);

namespace AiPageAssistant\Repository;

use WP_Post;
use WP_Query;

final class PageContentRepository
{
    /** @return array{id:int,title:string,url:string,content:string}|null */
    public function getById(int $postId): ?array
    {
        $post = get_post($postId);

        if (! $post instanceof WP_Post || $post->post_status !== 'publish') {
            return null;
        }

        return $this->mapPost($post);
    }

    /**
     * @param list<string> $keywords
     * @param list<string> $postTypes
     * @return list<array{id:int,title:string,url:string,content:string}>
     */
    public function searchByKeywords(array $keywords, array $postTypes, int $limit = 3, int $excludeId = 0): array
    {
        $queryText = implode(' ', array_slice($keywords, 0, 8));

        if ($queryText === '') {
            return [];
        }

        $query = new WP_Query([
            'post_type' => $postTypes ?: ['page', 'post'],
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            's' => $queryText,
            'post__not_in' => $excludeId > 0 ? [$excludeId] : [],
            'no_found_rows' => true,
        ]);

        $results = [];

        foreach ($query->posts as $post) {
            if ($post instanceof WP_Post) {
                $results[] = $this->mapPost($post);
            }
        }

        return $results;
    }

    /** @return array{id:int,title:string,url:string,content:string} */
    private function mapPost(WP_Post $post): array
    {
        $content = apply_filters('the_content', $post->post_content);
        $content = wp_strip_all_tags((string) $content);
        $content = preg_replace('/\\s+/', ' ', $content) ?? $content;

        return [
            'id' => (int) $post->ID,
            'title' => get_the_title($post),
            'url' => get_permalink($post) ?: '',
            'content' => trim($content),
        ];
    }
}
