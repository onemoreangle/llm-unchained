<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Store;

use OneMoreAngle\LlmUnchained\Document\Vector;
use OneMoreAngle\LlmUnchained\Document\VectorDocument;

interface VectorStoreInterface extends StoreInterface
{
    /**
     * @param array<string, mixed> $options
     *
     * @return VectorDocument[]
     */
    public function query(Vector $vector, array $options = [], ?float $minScore = null): array;
}
