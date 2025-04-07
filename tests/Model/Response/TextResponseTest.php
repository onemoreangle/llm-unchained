<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Model\Response;

use OneMoreAngle\LlmUnchained\Model\Response\TextModelResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(TextModelResponse::class)]
#[Small]
final class TextResponseTest extends TestCase
{
    #[Test]
    public function getContent(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $response = new TextModelResponse($responseMock, $expected = 'foo');
        self::assertSame($expected, $response->getContent());
    }
}
