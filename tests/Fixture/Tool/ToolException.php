<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Fixture\Tool;

use Exception;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Attribute\AsTool;

#[AsTool('tool_exception', description: 'This tool is broken', method: 'bar')]
final class ToolException
{
    public function bar(): string
    {
        throw new Exception('Tool error.');
    }
}
