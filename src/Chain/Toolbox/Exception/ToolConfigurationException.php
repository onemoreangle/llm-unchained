<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception;

use ReflectionException;
use OneMoreAngle\LlmUnchained\Exception\InvalidArgumentException;

final class ToolConfigurationException extends InvalidArgumentException implements ExceptionInterface
{
    public static function invalidMethod(string $toolClass, string $methodName, ReflectionException $previous): self
    {
        return new self(sprintf('Method "%s" not found in tool "%s".', $methodName, $toolClass), previous: $previous);
    }
}
