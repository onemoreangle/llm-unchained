<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception;

use OneMoreAngle\LlmUnchained\Chain\Toolbox\Attribute\AsTool;
use OneMoreAngle\LlmUnchained\Exception\InvalidArgumentException;

final class ToolMetadataException extends InvalidArgumentException implements ExceptionInterface
{
    public static function invalidReference(mixed $reference): self
    {
        return new self(sprintf('The reference "%s" is not a valid tool.', $reference));
    }

    public static function missingAttribute(string $className): self
    {
        return new self(sprintf('The class "%s" is not a tool, please add %s attribute.', $className, AsTool::class));
    }
}
