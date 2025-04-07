<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Fixture\Tool;

use OneMoreAngle\LlmUnchained\Chain\Toolbox\Attribute\AsTool;

#[AsTool('tool_without_docs', 'A tool with required parameters', method: 'bar')]
final class ToolWithoutDocs
{
    public function bar(string $text, int $number): string
    {
        return sprintf('%s says "%d".', $text, $number);
    }
}
