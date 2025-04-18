<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox\MetadataFactory;

use OneMoreAngle\LlmUnchained\Chain\Toolbox\Attribute\AsTool;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolMetadataException;

class MemoryFactory extends AbstractFactory
{
    /**
     * @var array<string, AsTool[]>
     */
    private array $tools = [];

    public function addTool(string|object $class, string $name, string $description, string $method = '__invoke'): self
    {
        $className = is_object($class) ? $class::class : $class;
        $this->tools[$className][] = new AsTool($name, $description, $method);

        return $this;
    }

    /**
     * @param class-string $reference
     */
    public function getMetadata(string $reference): iterable
    {
        if (!isset($this->tools[$reference])) {
            throw ToolMetadataException::invalidReference($reference);
        }

        foreach ($this->tools[$reference] as $tool) {
            yield $this->convertAttribute($reference, $tool);
        }
    }
}
