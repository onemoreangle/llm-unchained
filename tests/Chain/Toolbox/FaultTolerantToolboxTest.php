<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Chain\Toolbox;

use Exception;
use Closure;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolExecutionException;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolNotFoundException;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\ExecutionReference;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\FaultTolerantToolbox;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Metadata;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\ToolboxInterface;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCall;
use OneMoreAngle\LlmUnchained\Tests\Fixture\Tool\ToolNoParams;
use OneMoreAngle\LlmUnchained\Tests\Fixture\Tool\ToolRequiredParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FaultTolerantToolbox::class)]
#[UsesClass(ToolCall::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(ExecutionReference::class)]
#[UsesClass(ToolNotFoundException::class)]
#[UsesClass(ToolExecutionException::class)]
final class FaultTolerantToolboxTest extends TestCase
{
    #[Test]
    public function faultyToolExecution(): void
    {
        $faultyToolbox = $this->createFaultyToolbox(
            fn (ToolCall $toolCall) => ToolExecutionException::executionFailed($toolCall, new Exception('error'))
        );

        $faultTolerantToolbox = new FaultTolerantToolbox($faultyToolbox);
        $expected = 'An error occurred while executing tool "tool_foo".';

        $toolCall = new ToolCall('987654321', 'tool_foo');
        $actual = $faultTolerantToolbox->execute($toolCall);

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function faultyToolCall(): void
    {
        $faultyToolbox = $this->createFaultyToolbox(
            fn (ToolCall $toolCall) => ToolNotFoundException::notFoundForToolCall($toolCall)
        );

        $faultTolerantToolbox = new FaultTolerantToolbox($faultyToolbox);
        $expected = 'Tool "tool_xyz" was not found, please use one of these: tool_no_params, tool_required_params';

        $toolCall = new ToolCall('123456789', 'tool_xyz');
        $actual = $faultTolerantToolbox->execute($toolCall);

        self::assertSame($expected, $actual);
    }

    private function createFaultyToolbox(Closure $exceptionFactory): ToolboxInterface
    {
        return new class ($exceptionFactory) implements ToolboxInterface {
            public function __construct(private readonly Closure $exceptionFactory)
            {
            }

            /**
             * @return Metadata[]
             */
            public function getMap(): array
            {
                return [
                    new Metadata(new ExecutionReference(ToolNoParams::class), 'tool_no_params', 'A tool without parameters', null),
                    new Metadata(new ExecutionReference(ToolRequiredParams::class, 'bar'), 'tool_required_params', 'A tool with required parameters', null),
                ];
            }

            public function execute(ToolCall $toolCall): mixed
            {
                throw ($this->exceptionFactory)($toolCall);
            }
        };
    }
}
