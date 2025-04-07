<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Fixture\Tool;

use OneMoreAngle\LlmUnchained\Chain\Toolbox\Attribute\AsTool;

#[AsTool('tool_no_params', 'A tool without parameters')]
final class ToolArray
{
    /**
     * @param string[]  $urls
     * @param list<int> $ids
     */
    public function __invoke(array $urls, array $ids): string
    {
        return 'Hello world!';
    }
}
