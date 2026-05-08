<?php

declare(strict_types=1);

namespace AiPageAssistant\AI;

final class PromptBuilder
{
    public function __construct(private readonly string $customSystemPrompt = '')
    {
    }

    /**
     * @return list<array{role:string,content:string}>
     */
    public function build(string $context, string $userMessage, string $visitorLanguage): array
    {
        $language = $this->normalizeLanguage($visitorLanguage);
        $system = $this->customSystemPrompt !== '' ? $this->customSystemPrompt . "\n\n" : '';
        $system .= "You are an AI assistant embedded on a WordPress site. Answer only from the provided site context. ";
        $system .= "If the context is not enough, say so clearly and suggest contacting the site owner. ";
        $system .= "Use the visitor language: {$language}. Keep answers concise, friendly and practical. ";
        $system .= "Do not invent legal, medical or financial claims beyond the source text.";

        return [
            [
                'role' => 'system',
                'content' => $system,
            ],
            [
                'role' => 'user',
                'content' => "Site context:\n{$context}\n\nVisitor question:\n{$userMessage}",
            ],
        ];
    }

    private function normalizeLanguage(string $language): string
    {
        $language = strtolower(substr($language, 0, 2));

        return match ($language) {
            'de' => 'German',
            'uk', 'ua' => 'Ukrainian',
            default => 'English',
        };
    }
}
