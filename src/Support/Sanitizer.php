<?php

declare(strict_types=1);

namespace AiPageAssistant\Support;

final class Sanitizer
{
    public static function text(mixed $value, int $maxLength = 500): string
    {
        $text = is_scalar($value) ? (string) $value : '';
        $text = function_exists('wp_unslash') ? wp_unslash($text) : $text;
        $text = function_exists('sanitize_text_field') ? sanitize_text_field($text) : strip_tags($text);

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxLength);
        }

        return substr($text, 0, $maxLength);
    }

    public static function textarea(mixed $value, int $maxLength = 4000): string
    {
        $text = is_scalar($value) ? (string) $value : '';
        $text = function_exists('wp_unslash') ? wp_unslash($text) : $text;
        $text = function_exists('sanitize_textarea_field') ? sanitize_textarea_field($text) : strip_tags($text);

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxLength);
        }

        return substr($text, 0, $maxLength);
    }

    public static function int(mixed $value, int $min, int $max): int
    {
        $int = (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);

        return min($max, max($min, $int));
    }

    public static function bool(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }

    public static function key(mixed $value): string
    {
        $key = is_scalar($value) ? (string) $value : '';

        return function_exists('sanitize_key') ? sanitize_key($key) : strtolower(preg_replace('/[^a-zA-Z0-9_\\-]/', '', $key) ?? '');
    }

    public static function hexColor(mixed $value, string $fallback = '#2563eb'): string
    {
        $color = is_scalar($value) ? trim((string) $value) : '';

        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1) {
            return strtolower($color);
        }

        return $fallback;
    }

    /** @param list<string> $allowed */
    public static function choice(mixed $value, array $allowed, string $fallback): string
    {
        $choice = is_scalar($value) ? (string) $value : '';

        return in_array($choice, $allowed, true) ? $choice : $fallback;
    }
}
