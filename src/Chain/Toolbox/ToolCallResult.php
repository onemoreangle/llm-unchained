<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox;

use OneMoreAngle\LlmUnchained\Model\Response\ToolCall;

final readonly class ToolCallResult
{
    public function __construct(
        public ToolCall $toolCall,
        public mixed $result,
    ) {
    }
}
