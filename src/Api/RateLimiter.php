<?php

declare(strict_types=1);

namespace AiPageAssistant\Api;

use AiPageAssistant\Support\IpAnonymizer;
use AiPageAssistant\Support\Settings;

final class RateLimiter
{
    public function __construct(
        private readonly Settings $settings,
        private readonly IpAnonymizer $ipAnonymizer = new IpAnonymizer()
    ) {
    }

    /** @return array{allowed:bool,hour_remaining:int,day_remaining:int,retry_after:int} */
    public function checkAndIncrement(string $ip): array
    {
        $hash = $this->ipAnonymizer->hash($ip);
        $hourKey = 'ai_pa_rl_h_' . $hash;
        $dayKey = 'ai_pa_rl_d_' . $hash;

        $hourLimit = $this->settings->hourlyLimit();
        $dayLimit = $this->settings->dailyLimit();
        $hourCount = (int) get_transient($hourKey);
        $dayCount = (int) get_transient($dayKey);

        if ($hourCount >= $hourLimit || $dayCount >= $dayLimit) {
            return [
                'allowed' => false,
                'hour_remaining' => max(0, $hourLimit - $hourCount),
                'day_remaining' => max(0, $dayLimit - $dayCount),
                'retry_after' => HOUR_IN_SECONDS,
            ];
        }

        set_transient($hourKey, $hourCount + 1, HOUR_IN_SECONDS);
        set_transient($dayKey, $dayCount + 1, DAY_IN_SECONDS);

        return [
            'allowed' => true,
            'hour_remaining' => max(0, $hourLimit - $hourCount - 1),
            'day_remaining' => max(0, $dayLimit - $dayCount - 1),
            'retry_after' => 0,
        ];
    }
}
