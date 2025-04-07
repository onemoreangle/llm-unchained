<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\InputProcessor;

use Stringable;

final class SystemPromptService implements Stringable
{
    public function __toString(): string
    {
        return 'My dynamic system prompt.';
    }
}
