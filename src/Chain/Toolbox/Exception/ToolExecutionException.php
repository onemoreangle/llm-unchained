<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception;

use RuntimeException;
use Throwable;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCall;

class ToolExecutionException extends RuntimeException implements ExceptionInterface
{
    public ?ToolCall $toolCall = null;

    public static function executionFailed(ToolCall $toolCall, Throwable $previous): self
    {
        $exception = new self(sprintf('Execution of tool "%s" failed with error: %s', $toolCall->name, $previous->getMessage()), previous: $previous);
        $exception->toolCall = $toolCall;

        return $exception;
    }
}
