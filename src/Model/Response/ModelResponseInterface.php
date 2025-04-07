<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Response;

use Symfony\Contracts\HttpClient\ResponseInterface;

interface ModelResponseInterface
{
    /**
     * @return string|iterable<mixed>|object|null
     */
    public function getContent(): string|iterable|object|null;

    public function getRawResponse(): ResponseInterface;
}
