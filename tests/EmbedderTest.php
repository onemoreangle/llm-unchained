<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests;

use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Embeddings;
use OneMoreAngle\LlmUnchained\Document\Metadata;
use OneMoreAngle\LlmUnchained\Document\TextDocument;
use OneMoreAngle\LlmUnchained\Document\Vector;
use OneMoreAngle\LlmUnchained\Document\VectorDocument;
use OneMoreAngle\LlmUnchained\Embedder;
use OneMoreAngle\LlmUnchained\Model\Message\ToolCallMessage;
use OneMoreAngle\LlmUnchained\Model\Response\AsyncModelResponse;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCall;
use OneMoreAngle\LlmUnchained\Model\Response\VectorModelResponse;
use OneMoreAngle\LlmUnchained\Platform;
use OneMoreAngle\LlmUnchained\Tests\Double\PlatformTestHandler;
use OneMoreAngle\LlmUnchained\Tests\Double\TestStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(Embedder::class)]
#[Medium]
#[UsesClass(TextDocument::class)]
#[UsesClass(Vector::class)]
#[UsesClass(VectorDocument::class)]
#[UsesClass(ToolCallMessage::class)]
#[UsesClass(ToolCall::class)]
#[UsesClass(Embeddings::class)]
#[UsesClass(Platform::class)]
#[UsesClass(AsyncModelResponse::class)]
#[UsesClass(VectorModelResponse::class)]
final class EmbedderTest extends TestCase
{
    #[Test]
    public function embedSingleDocument(): void
    {
        $document = new TextDocument($id = Uuid::v4(), 'Test content');
        $vector = new Vector([0.1, 0.2, 0.3]);

        $responseMock = $this->createMock(ResponseInterface::class);
        $embedder = new Embedder(
            PlatformTestHandler::createPlatform(new VectorModelResponse($responseMock, $vector)),
            new Embeddings(),
            $store = new TestStore(),
            new MockClock(),
        );

        $embedder->embed($document);

        self::assertCount(1, $store->documents);
        self::assertInstanceOf(VectorDocument::class, $store->documents[0]);
        self::assertSame($id, $store->documents[0]->id);
        self::assertSame($vector, $store->documents[0]->vector);
    }

    #[Test]
    public function embedEmptyDocumentList(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('debug')->with('No documents to embed');

        $embedder = new Embedder(
            PlatformTestHandler::createPlatform(),
            new Embeddings(),
            $store = new TestStore(),
            new MockClock(),
            $logger,
        );

        $embedder->embed([]);

        self::assertSame([], $store->documents);
    }

    #[Test]
    public function embedDocumentWithMetadata(): void
    {
        $metadata = new Metadata(['key' => 'value']);
        $document = new TextDocument($id = Uuid::v4(), 'Test content', $metadata);
        $vector = new Vector([0.1, 0.2, 0.3]);

        $responseMock = $this->createMock(ResponseInterface::class);
        $embedder = new Embedder(
            PlatformTestHandler::createPlatform(new VectorModelResponse($responseMock, $vector)),
            new Embeddings(),
            $store = new TestStore(),
            new MockClock(),
        );

        $embedder->embed($document);

        self::assertSame(1, $store->addCalls);
        self::assertCount(1, $store->documents);
        self::assertInstanceOf(VectorDocument::class, $store->documents[0]);
        self::assertSame($id, $store->documents[0]->id);
        self::assertSame($vector, $store->documents[0]->vector);
        self::assertSame(['key' => 'value'], $store->documents[0]->metadata->getArrayCopy());
    }

    #[Test]
    public function embedWithSleep(): void
    {
        $vector1 = new Vector([0.1, 0.2, 0.3]);
        $vector2 = new Vector([0.4, 0.5, 0.6]);

        $document1 = new TextDocument(Uuid::v4(), 'Test content 1');
        $document2 = new TextDocument(Uuid::v4(), 'Test content 2');

        $responseMock = $this->createMock(ResponseInterface::class);
        $embedder = new Embedder(
            PlatformTestHandler::createPlatform(new VectorModelResponse($responseMock, $vector1, $vector2)),
            new Embeddings(),
            $store = new TestStore(),
            $clock = new MockClock('2024-01-01 00:00:00'),
        );

        $embedder->embed(
            documents: [$document1, $document2],
            sleep: 3
        );

        self::assertSame(1, $store->addCalls);
        self::assertCount(2, $store->documents);
        self::assertSame('2024-01-01 00:00:03', $clock->now()->format('Y-m-d H:i:s'));
    }
}
