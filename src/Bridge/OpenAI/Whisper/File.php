<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\OpenAI\Whisper;

use OneMoreAngle\LlmUnchained\Exception\InvalidArgumentException;

final readonly class File
{
    public function __construct(
        public string $path,
    ) {
        if (!is_readable($path) || false === file_get_contents($path)) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not exist or is not readable.', $path));
        }
    }
}
