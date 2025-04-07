<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Store;

use OneMoreAngle\LlmUnchained\Document\VectorDocument;

interface StoreInterface
{
    public function add(VectorDocument ...$documents): void;
}
