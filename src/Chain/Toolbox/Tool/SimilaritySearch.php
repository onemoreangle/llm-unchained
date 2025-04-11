<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox\Tool;

use OneMoreAngle\LlmUnchained\Chain\Toolbox\Attribute\AsTool;
use OneMoreAngle\LlmUnchained\Document\Vector;
use OneMoreAngle\LlmUnchained\Document\VectorDocument;
use OneMoreAngle\LlmUnchained\Model\EmbeddingsModel;
use OneMoreAngle\LlmUnchained\PlatformInterface;
use OneMoreAngle\LlmUnchained\Store\VectorStoreInterface;

#[AsTool('similarity_search', description: 'Searches for documents similar to a query or sentence.')]
class SimilaritySearch
{
    /**
     * @var VectorDocument[]
     */
    public array $usedDocuments = [];

    public function __construct(
        private readonly PlatformInterface $platform,
        private readonly EmbeddingsModel $embeddings,
        private readonly VectorStoreInterface $vectorStore,
    ) {
    }

    /**
     * @param string $searchTerm string used for similarity search
     */
    public function __invoke(string $searchTerm): string
    {
        /** @var Vector[] $vectors */
        $vectors = $this->platform->request($this->embeddings, $searchTerm)->getContent();
        $this->usedDocuments = $this->vectorStore->query($vectors[0]);

        if (0 === count($this->usedDocuments)) {
            return 'No results found';
        }

        $result = 'Found documents with following information:'."\n";
        foreach ($this->usedDocuments as $document) {
            $result .= json_encode($document->metadata);
        }

        return $result;
    }
}
