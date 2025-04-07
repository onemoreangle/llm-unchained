<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox;

use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolExecutionException;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolNotFoundException;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCall;

interface ToolboxInterface
{
    /**
     * @return Metadata[]
     */
    public function getMap(): array;

    /**
     * @throws ToolExecutionException if the tool execution fails
     * @throws ToolNotFoundException  if the tool is not found
     */
    public function execute(ToolCall $toolCall): mixed;
}
