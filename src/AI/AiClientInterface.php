<?php

declare(strict_types=1);

namespace AiPageAssistant\AI;

interface AiClientInterface
{
    /**
     * @param list<array{role:string,content:string}> $messages
     * @return \Generator<string>
     */
    public function streamChat(array $messages): \Generator;
}
