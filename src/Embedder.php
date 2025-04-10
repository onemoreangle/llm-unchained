<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained;

use OneMoreAngle\LlmUnchained\Document\TextDocument;
use OneMoreAngle\LlmUnchained\Document\VectorDocument;
use OneMoreAngle\LlmUnchained\Model\EmbeddingsModel;
use OneMoreAngle\LlmUnchained\Store\StoreInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\ClockInterface;

readonly class Embedder
{
    private ClockInterface $clock;

    public function __construct(
        private PlatformInterface $platform,
        private EmbeddingsModel $embeddings,
        private StoreInterface $store,
        ?ClockInterface $clock = null,
        private LoggerInterface $logger = new NullLogger(),
    ) {
        $this->clock = $clock ?? Clock::get();
    }

    /**
     * @param TextDocument|TextDocument[] $documents
     */
    public function embed(TextDocument|array $documents, int $chunkSize = 0, int $sleep = 0): void
    {
        if ($documents instanceof TextDocument) {
            $documents = [$documents];
        }

        if ([] === $documents) {
            $this->logger->debug('No documents to embed');

            return;
        }

        $chunks = 0 !== $chunkSize ? array_chunk($documents, $chunkSize) : [$documents];

        foreach ($chunks as $chunk) {
            $this->store->add(...$this->createVectorDocuments($chunk));

            if (0 !== $sleep) {
                $this->clock->sleep($sleep);
            }
        }
    }

    /**
     * @param TextDocument[] $documents
     *
     * @return VectorDocument[]
     */
    private function createVectorDocuments(array $documents): array
    {
        if ($this->embeddings->supportsMultipleInputs()) {
            $response = $this->platform->request($this->embeddings, array_map(fn (TextDocument $document) => $document->content, $documents));

            $vectors = $response->getContent();
        } else {
            $responses = [];
            foreach ($documents as $document) {
                $responses[] = $this->platform->request($this->embeddings, $document->content);
            }

            $vectors = [];
            foreach ($responses as $response) {
                $vectors = array_merge($vectors, $response->getContent());
            }
        }

        $vectorDocuments = [];
        foreach ($documents as $i => $document) {
            $vectorDocuments[] = new VectorDocument($document->id, $vectors[$i], $document->metadata);
        }

        return $vectorDocuments;
    }
}
