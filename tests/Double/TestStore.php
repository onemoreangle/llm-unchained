<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Double;

use OneMoreAngle\LlmUnchained\Document\VectorDocument;
use OneMoreAngle\LlmUnchained\Store\StoreInterface;

final class TestStore implements StoreInterface
{
    /**
     * @var VectorDocument[]
     */
    public array $documents = [];

    public int $addCalls = 0;

    public function add(VectorDocument ...$documents): void
    {
        ++$this->addCalls;
        $this->documents = array_merge($this->documents, $documents);
    }
}
