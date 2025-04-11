<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Document;

use OneMoreAngle\LlmUnchained\Exception\RuntimeException;

class NullVector implements VectorInterface
{
    public function getData(): array
    {
        throw new RuntimeException('getData() method cannot be called on a NullVector.');
    }

    public function getDimensions(): int
    {
        throw new RuntimeException('getDimensions() method cannot be called on a NullVector.');
    }
}
