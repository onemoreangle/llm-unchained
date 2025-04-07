<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Model\Response;

use OneMoreAngle\LlmUnchained\Exception\InvalidArgumentException;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCall;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCallResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolCallResponse::class)]
#[UsesClass(ToolCall::class)]
#[Small]
final class TollCallResponseTest extends TestCase
{
    #[Test]
    public function throwsIfNoToolCall(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must have at least one tool call.');

        new ToolCallResponse();
    }

    #[Test]
    public function getContent(): void
    {
        $response = new ToolCallResponse($toolCall = new ToolCall('ID', 'name', ['foo' => 'bar']));
        self::assertSame([$toolCall], $response->getContent());
    }
}
