<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Chain\Toolbox\MetadataFactory;

use OneMoreAngle\LlmUnchained\Chain\JsonSchema\DescriptionParser;
use OneMoreAngle\LlmUnchained\Chain\JsonSchema\Factory;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Attribute\AsTool;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolConfigurationException;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolMetadataException;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\ExecutionReference;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Metadata;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\MetadataFactory\ReflectionFactory;
use OneMoreAngle\LlmUnchained\Tests\Fixture\Tool\ToolMultiple;
use OneMoreAngle\LlmUnchained\Tests\Fixture\Tool\ToolRequiredParams;
use OneMoreAngle\LlmUnchained\Tests\Fixture\Tool\ToolWrong;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReflectionFactory::class)]
#[UsesClass(AsTool::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(ExecutionReference::class)]
#[UsesClass(Factory::class)]
#[UsesClass(DescriptionParser::class)]
#[UsesClass(ToolConfigurationException::class)]
#[UsesClass(ToolMetadataException::class)]
final class ReflectionFactoryTest extends TestCase
{
    private ReflectionFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ReflectionFactory();
    }

    #[Test]
    public function invalidReferenceNonExistingClass(): void
    {
        $this->expectException(ToolMetadataException::class);
        $this->expectExceptionMessage('The reference "invalid" is not a valid tool.');

        iterator_to_array($this->factory->getMetadata('invalid')); // @phpstan-ignore-line Yes, this class does not exist
    }

    #[Test]
    public function withoutAttribute(): void
    {
        $this->expectException(ToolMetadataException::class);
        $this->expectExceptionMessage(
            sprintf('The class "%s" is not a tool, please add %s attribute.', ToolWrong::class, AsTool::class)
        );

        iterator_to_array($this->factory->getMetadata(ToolWrong::class));
    }

    #[Test]
    public function getDefinition(): void
    {
        /** @var Metadata[] $metadatas */
        $metadatas = iterator_to_array($this->factory->getMetadata(ToolRequiredParams::class));

        self::assertToolConfiguration(
            metadata: $metadatas[0],
            className: ToolRequiredParams::class,
            name: 'tool_required_params',
            description: 'A tool with required parameters',
            method: 'bar',
            parameters: [
                'type' => 'object',
                'properties' => [
                    'text' => [
                        'type' => 'string',
                        'description' => 'The text given to the tool',
                    ],
                    'number' => [
                        'type' => 'integer',
                        'description' => 'A number given to the tool',
                    ],
                ],
                'required' => ['text', 'number'],
                'additionalProperties' => false,
            ],
        );
    }

    #[Test]
    public function getDefinitionWithMultiple(): void
    {
        $metadatas = iterator_to_array($this->factory->getMetadata(ToolMultiple::class));

        self::assertCount(2, $metadatas);

        [$first, $second] = $metadatas;

        self::assertToolConfiguration(
            metadata: $first,
            className: ToolMultiple::class,
            name: 'tool_hello_world',
            description: 'Function to say hello',
            method: 'hello',
            parameters: [
                'type' => 'object',
                'properties' => [
                    'world' => [
                        'type' => 'string',
                        'description' => 'The world to say hello to',
                    ],
                ],
                'required' => ['world'],
                'additionalProperties' => false,
            ],
        );

        self::assertToolConfiguration(
            metadata: $second,
            className: ToolMultiple::class,
            name: 'tool_required_params',
            description: 'Function to say a number',
            method: 'bar',
            parameters: [
                'type' => 'object',
                'properties' => [
                    'text' => [
                        'type' => 'string',
                        'description' => 'The text given to the tool',
                    ],
                    'number' => [
                        'type' => 'integer',
                        'description' => 'A number given to the tool',
                    ],
                ],
                'required' => ['text', 'number'],
                'additionalProperties' => false,
            ],
        );
    }

    private function assertToolConfiguration(Metadata $metadata, string $className, string $name, string $description, string $method, array $parameters): void
    {
        self::assertSame($className, $metadata->reference->class);
        self::assertSame($method, $metadata->reference->method);
        self::assertSame($name, $metadata->name);
        self::assertSame($description, $metadata->description);
        self::assertSame($parameters, $metadata->parameters);
    }
}
