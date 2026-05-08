<?php

declare(strict_types=1);

namespace AiPageAssistant\Tests;

use AiPageAssistant\AI\PromptBuilder;
use PHPUnit\Framework\TestCase;

final class PromptBuilderTest extends TestCase
{
    public function testBuildsGermanSystemPrompt(): void
    {
        $messages = (new PromptBuilder())->build('Page context', 'Was bedeutet das?', 'de-DE');

        self::assertSame('system', $messages[0]['role']);
        self::assertStringContainsString('German', $messages[0]['content']);
        self::assertStringContainsString('Page context', $messages[1]['content']);
        self::assertStringContainsString('Was bedeutet das?', $messages[1]['content']);
    }

    public function testCustomPromptIsPrepended(): void
    {
        $messages = (new PromptBuilder('Custom client rule.'))->build('Context', 'Question', 'en');

        self::assertStringStartsWith('Custom client rule.', $messages[0]['content']);
    }
}
