<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Document;

interface VectorInterface
{
    /**
     * @return list<float>
     */
    public function getData(): array;

    public function getDimensions(): int;
}
