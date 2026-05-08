<?php

declare(strict_types=1);

namespace AiPageAssistant\AI;

use AiPageAssistant\Repository\PageContentRepository;
use AiPageAssistant\Support\Settings;

final class ContextBuilder
{
    public function __construct(
        private readonly PageContentRepository $pages,
        private readonly Settings $settings,
        private readonly int $maxChars = 24000
    ) {
    }

    /** @param array{title?:string,url?:string,text?:string} $clientPage */
    public function buildForQuery(int $currentPageId, string $userMessage, array $clientPage = []): string
    {
        $sections = [];
        $current = $this->pages->getById($currentPageId);

        if ($current !== null) {
            $sections[] = $this->formatSection('Current page', $current);
        } elseif (($clientPage['text'] ?? '') !== '') {
            $sections[] = $this->formatClientSection($clientPage);
        }

        $related = $this->pages->searchByKeywords(
            $this->extractKeywords($userMessage),
            $this->settings->enabledPostTypes(),
            3,
            $currentPageId
        );

        foreach ($related as $page) {
            $sections[] = $this->formatSection('Related page', $page);
        }

        $context = implode("\n\n---\n\n", $sections);

        return $this->trimToBudget($context);
    }

    /** @param array{title?:string,url?:string,text?:string} $page */
    private function formatClientSection(array $page): string
    {
        return sprintf(
            "Current browser page\nTitle: %s\nURL: %s\nVisible content:\n%s",
            $page['title'] ?? '',
            $page['url'] ?? '',
            $page['text'] ?? ''
        );
    }

    /** @return list<string> */
    private function extractKeywords(string $message): array
    {
        $message = strtolower($message);
        $message = preg_replace('/[^\\p{L}\\p{N}\\s-]/u', ' ', $message) ?? $message;
        $words = preg_split('/\\s+/u', $message, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $stopWords = ['the', 'and', 'oder', 'und', 'що', 'це', 'для', 'with', 'about', 'eine', 'einer', 'der', 'die', 'das'];

        $keywords = [];

        foreach ($words as $word) {
            if (mb_strlen($word) < 4 || in_array($word, $stopWords, true)) {
                continue;
            }

            $keywords[] = $word;
        }

        return array_values(array_unique(array_slice($keywords, 0, 12)));
    }

    /** @param array{id:int,title:string,url:string,content:string} $page */
    private function formatSection(string $label, array $page): string
    {
        return sprintf(
            "%s\nTitle: %s\nURL: %s\nContent:\n%s",
            $label,
            $page['title'],
            $page['url'],
            $page['content']
        );
    }

    private function trimToBudget(string $context): string
    {
        if (mb_strlen($context) <= $this->maxChars) {
            return $context;
        }

        return mb_substr($context, 0, $this->maxChars) . "\n\n[Context truncated to fit budget.]";
    }
}
