<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox\MetadataFactory;

use ReflectionClass;
use OneMoreAngle\LlmUnchained\Chain\JsonSchema\Factory;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Attribute\AsTool;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolMetadataException;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Metadata;

/**
 * Metadata factory that uses reflection in combination with `#[AsTool]` attribute to extract metadata from tools.
 */
final class ReflectionFactory extends AbstractFactory
{
    /**
     * @param class-string $reference
     */
    public function getMetadata(string $reference): iterable
    {
        if (!class_exists($reference)) {
            throw ToolMetadataException::invalidReference($reference);
        }

        $reflectionClass = new ReflectionClass($reference);
        $attributes = $reflectionClass->getAttributes(AsTool::class);

        if (0 === count($attributes)) {
            throw ToolMetadataException::missingAttribute($reference);
        }

        foreach ($attributes as $attribute) {
            yield $this->convertAttribute($reference, $attribute->newInstance());
        }
    }
}
