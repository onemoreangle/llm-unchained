<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Chain\Toolbox;

use OneMoreAngle\LlmUnchained\Chain\JsonSchema\DescriptionParser;
use OneMoreAngle\LlmUnchained\Chain\JsonSchema\Factory;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Attribute\AsTool;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolConfigurationException;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolExecutionException;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolNotFoundException;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\ExecutionReference;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Metadata;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\MetadataFactory\ChainFactory;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\MetadataFactory\MemoryFactory;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\MetadataFactory\ReflectionFactory;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Toolbox;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCall;
use OneMoreAngle\LlmUnchained\Tests\Fixture\Tool\ToolException;
use OneMoreAngle\LlmUnchained\Tests\Fixture\Tool\ToolMisconfigured;
use OneMoreAngle\LlmUnchained\Tests\Fixture\Tool\ToolNoAttribute1;
use OneMoreAngle\LlmUnchained\Tests\Fixture\Tool\ToolNoParams;
use OneMoreAngle\LlmUnchained\Tests\Fixture\Tool\ToolOptionalParam;
use OneMoreAngle\LlmUnchained\Tests\Fixture\Tool\ToolRequiredParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Toolbox::class)]
#[UsesClass(ToolCall::class)]
#[UsesClass(AsTool::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(ExecutionReference::class)]
#[UsesClass(ReflectionFactory::class)]
#[UsesClass(MemoryFactory::class)]
#[UsesClass(ChainFactory::class)]
#[UsesClass(Factory::class)]
#[UsesClass(DescriptionParser::class)]
#[UsesClass(ToolConfigurationException::class)]
#[UsesClass(ToolNotFoundException::class)]
#[UsesClass(ToolExecutionException::class)]
final class ToolboxTest extends TestCase
{
    private Toolbox $toolbox;

    protected function setUp(): void
    {
        $this->toolbox = new Toolbox(new ReflectionFactory(), [
            new ToolRequiredParams(),
            new ToolOptionalParam(),
            new ToolNoParams(),
            new ToolException(),
        ]);
    }

    #[Test]
    public function toolsMap(): void
    {
        $actual = $this->toolbox->getMap();
        $expected = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'tool_required_params',
                    'description' => 'A tool with required parameters',
                    'parameters' => [
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
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'tool_optional_param',
                    'description' => 'A tool with one optional parameter',
                    'parameters' => [
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
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'tool_no_params',
                    'description' => 'A tool without parameters',
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'tool_exception',
                    'description' => 'This tool is broken',
                ],
            ],
        ];

        self::assertSame(json_encode($expected), json_encode($actual));
    }

    #[Test]
    public function executeWithUnknownTool(): void
    {
        self::expectException(ToolNotFoundException::class);
        self::expectExceptionMessage('Tool not found for call: foo_bar_baz');

        $this->toolbox->execute(new ToolCall('call_1234', 'foo_bar_baz'));
    }

    #[Test]
    public function executeWithMisconfiguredTool(): void
    {
        self::expectException(ToolConfigurationException::class);
        self::expectExceptionMessage('Method "foo" not found in tool "OneMoreAngle\LlmUnchained\Tests\Fixture\Tool\ToolMisconfigured".');

        $toolbox = new Toolbox(new ReflectionFactory(), [new ToolMisconfigured()]);

        $toolbox->execute(new ToolCall('call_1234', 'tool_misconfigured'));
    }

    #[Test]
    public function executeWithException(): void
    {
        self::expectException(ToolExecutionException::class);
        self::expectExceptionMessage('Execution of tool "tool_exception" failed with error: Tool error.');

        $this->toolbox->execute(new ToolCall('call_1234', 'tool_exception'));
    }

    #[Test]
    #[DataProvider('executeProvider')]
    public function execute(string $expected, string $toolName, array $toolPayload = []): void
    {
        self::assertSame(
            $expected,
            $this->toolbox->execute(new ToolCall('call_1234', $toolName, $toolPayload)),
        );
    }

    /**
     * @return iterable<array{0: non-empty-string, 1: non-empty-string, 2?: array}>
     */
    public static function executeProvider(): iterable
    {
        yield 'tool_required_params' => [
            'Hello says "3".',
            'tool_required_params',
            ['text' => 'Hello', 'number' => 3],
        ];
    }

    #[Test]
    public function toolboxMapWithMemoryFactory(): void
    {
        $memoryFactory = (new MemoryFactory())
            ->addTool(ToolNoAttribute1::class, 'happy_birthday', 'Generates birthday message');

        $toolbox = new Toolbox($memoryFactory, [new ToolNoAttribute1()]);
        $expected = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'happy_birthday',
                    'description' => 'Generates birthday message',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                'description' => 'the name of the person',
                            ],
                            'years' => [
                                'type' => 'integer',
                                'description' => 'the age of the person',
                            ],
                        ],
                        'required' => ['name', 'years'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];

        self::assertSame(json_encode($expected), json_encode($toolbox->getMap()));
    }

    #[Test]
    public function toolboxExecutionWithMemoryFactory(): void
    {
        $memoryFactory = (new MemoryFactory())
            ->addTool(ToolNoAttribute1::class, 'happy_birthday', 'Generates birthday message');

        $toolbox = new Toolbox($memoryFactory, [new ToolNoAttribute1()]);
        $response = $toolbox->execute(new ToolCall('call_1234', 'happy_birthday', ['name' => 'John', 'years' => 30]));

        self::assertSame('Happy Birthday, John! You are 30 years old.', $response);
    }

    #[Test]
    public function toolboxMapWithOverrideViaChain(): void
    {
        $factory1 = (new MemoryFactory())
            ->addTool(ToolOptionalParam::class, 'optional_param', 'Tool with optional param', 'bar');
        $factory2 = new ReflectionFactory();

        $toolbox = new Toolbox(new ChainFactory([$factory1, $factory2]), [new ToolOptionalParam()]);

        $expected = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'optional_param',
                    'description' => 'Tool with optional param',
                    'parameters' => [
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
                ],
            ],
        ];

        self::assertSame(json_encode($expected), json_encode($toolbox->getMap()));
    }
}
