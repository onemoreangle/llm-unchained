<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Response;

use OneMoreAngle\LlmUnchained\Document\Vector;

final readonly class VectorResponse implements ResponseInterface
{
    /**
     * @var Vector[]
     */
    private array $vectors;

    public function __construct(Vector ...$vector)
    {
        $this->vectors = $vector;
    }

    /**
     * @return Vector[]
     */
    public function getContent(): array
    {
        return $this->vectors;
    }
}
