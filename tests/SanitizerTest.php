<?php

declare(strict_types=1);

namespace AiPageAssistant\Tests;

use AiPageAssistant\Support\Sanitizer;
use PHPUnit\Framework\TestCase;

final class SanitizerTest extends TestCase
{
    public function testTextRemovesHtmlAndLimitsLength(): void
    {
        $result = Sanitizer::text('<script>alert(1)</script>Hello world', 12);

        self::assertStringNotContainsString('<script>', $result);
        self::assertLessThanOrEqual(12, strlen($result));
    }

    public function testHexColorFallsBackForInvalidInput(): void
    {
        self::assertSame('#2563eb', Sanitizer::hexColor('javascript:alert(1)'));
        self::assertSame('#ff00aa', Sanitizer::hexColor('#ff00aa'));
    }

    public function testChoiceRejectsUnknownValues(): void
    {
        self::assertSame('safe', Sanitizer::choice('evil', ['safe'], 'safe'));
    }
}
