<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\OpenAI;

use OneMoreAngle\LlmUnchained\Model\Model;

readonly class Whisper implements Model
{
    public const WHISPER_1 = 'whisper-1';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private string $version = self::WHISPER_1,
        private array $options = [],
    ) {
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
