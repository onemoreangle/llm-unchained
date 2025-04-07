<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Fixture\Tool;

use OneMoreAngle\LlmUnchained\Chain\Toolbox\Attribute\AsTool;

#[AsTool('tool_misconfigured', description: 'This tool is misconfigured, see method', method: 'foo')]
final class ToolMisconfigured
{
    public function bar(): string
    {
        return 'Wrong Config Attribute';
    }
}
