<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox\Event;

use OneMoreAngle\LlmUnchained\Chain\Toolbox\ToolCallResult;
use OneMoreAngle\LlmUnchained\Model\Response\ResponseInterface;

final class ToolCallsExecuted
{
    /**
     * @var ToolCallResult[]
     */
    public readonly array $toolCallResults;
    public ResponseInterface $response;

    public function __construct(ToolCallResult ...$toolCallResults)
    {
        $this->toolCallResults = $toolCallResults;
    }

    public function hasResponse(): bool
    {
        return isset($this->response);
    }
}
