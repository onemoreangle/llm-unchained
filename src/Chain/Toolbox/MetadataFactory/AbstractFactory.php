<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox\MetadataFactory;

use ReflectionException;
use OneMoreAngle\LlmUnchained\Chain\JsonSchema\Factory;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Attribute\AsTool;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolConfigurationException;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\ExecutionReference;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Metadata;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\MetadataFactory;

abstract class AbstractFactory implements MetadataFactory
{
    public function __construct(
        private readonly Factory $factory = new Factory(),
    ) {
    }

    protected function convertAttribute(string $className, AsTool $attribute): Metadata
    {
        try {
            return new Metadata(
                new ExecutionReference($className, $attribute->method),
                $attribute->name,
                $attribute->description,
                $this->factory->buildParameters($className, $attribute->method)
            );
        } catch (ReflectionException $e) {
            throw ToolConfigurationException::invalidMethod($className, $attribute->method, $e);
        }
    }
}
