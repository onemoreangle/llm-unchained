<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Model\Response;

use Generator;
use OneMoreAngle\LlmUnchained\Model\Response\StreamModelResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(StreamModelResponse::class)]
#[Small]
final class StreamResponseTest extends TestCase
{
    #[Test]
    public function getContent(): void
    {
        $generator = (function () {
            yield 'data1';
            yield 'data2';
        })();

        $responseMock = $this->createMock(ResponseInterface::class);

        $response = new StreamModelResponse($responseMock, $generator);
        self::assertInstanceOf(Generator::class, $response->getContent());

        $content = iterator_to_array($response->getContent());

        self::assertCount(2, $content);
        self::assertSame('data1', $content[0]);
        self::assertSame('data2', $content[1]);
    }
}
