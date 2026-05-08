<?php

declare(strict_types=1);

namespace AiPageAssistant\Support;

final class IpAnonymizer
{
    public function hash(string $ip): string
    {
        $salt = defined('AUTH_SALT') ? (string) AUTH_SALT : (defined('NONCE_SALT') ? (string) NONCE_SALT : 'ai-page-assistant');

        return hash('sha256', $ip . '|' . $salt);
    }

    public function anonymize(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = '0';

            return implode('.', $parts);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);

            return implode(':', array_slice($parts, 0, 4)) . '::';
        }

        return '';
    }
}
