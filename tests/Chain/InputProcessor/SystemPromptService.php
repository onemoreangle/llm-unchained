<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Chain\InputProcessor;

use Stringable;

final class SystemPromptService implements Stringable
{
    public function __toString(): string
    {
        return 'My dynamic system prompt.';
    }
}
