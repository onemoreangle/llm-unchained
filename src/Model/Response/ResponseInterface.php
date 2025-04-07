<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Response;

interface ResponseInterface
{
    /**
     * @return string|iterable<mixed>|object|null
     */
    public function getContent(): string|iterable|object|null;
}
