<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Model\Response;

use OneMoreAngle\LlmUnchained\Model\Response\StructuredModelResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(StructuredModelResponse::class)]
#[Small]
final class StructuredResponseTest extends TestCase
{
    #[Test]
    public function getContentWithArray(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $response = new StructuredModelResponse($mockResponse, $expected = ['foo' => 'bar', 'baz' => ['qux']]);
        self::assertSame($expected, $response->getContent());
    }

    #[Test]
    public function getContentWithObject(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $response = new StructuredModelResponse($mockResponse, $expected = (object) ['foo' => 'bar', 'baz' => ['qux']]);
        self::assertSame($expected, $response->getContent());
    }
}
