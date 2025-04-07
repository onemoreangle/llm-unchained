<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception;

use RuntimeException;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\ExecutionReference;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCall;

final class ToolNotFoundException extends RuntimeException implements ExceptionInterface
{
    public ?ToolCall $toolCall = null;

    public static function notFoundForToolCall(ToolCall $toolCall): self
    {
        $exception = new self(sprintf('Tool not found for call: %s.', $toolCall->name));
        $exception->toolCall = $toolCall;

        return $exception;
    }

    public static function notFoundForReference(ExecutionReference $reference): self
    {
        return new self(sprintf('Tool not found for reference: %s::%s.', $reference->class, $reference->method));
    }
}
