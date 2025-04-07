<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Model\Response;

use OneMoreAngle\LlmUnchained\Exception\InvalidArgumentException;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCall;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCallModelResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(ToolCallModelResponse::class)]
#[UsesClass(ToolCall::class)]
#[Small]
final class TollCallResponseTest extends TestCase
{
    #[Test]
    public function throwsIfNoToolCall(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must have at least one tool call.');

        $responseMock = $this->createMock(ResponseInterface::class);
        new ToolCallModelResponse($responseMock);
    }

    #[Test]
    public function getContent(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $response = new ToolCallModelResponse($responseMock, $toolCall = new ToolCall('ID', 'name', ['foo' => 'bar']));
        self::assertSame([$toolCall], $response->getContent());
    }
}
