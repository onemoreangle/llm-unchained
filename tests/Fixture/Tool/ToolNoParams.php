<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Fixture\Tool;

use OneMoreAngle\LlmUnchained\Chain\Toolbox\Attribute\AsTool;

#[AsTool('tool_no_params', 'A tool without parameters')]
final class ToolNoParams
{
    public function __invoke(): string
    {
        return 'Hello world!';
    }
}
