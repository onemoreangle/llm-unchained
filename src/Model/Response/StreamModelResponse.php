<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Response;

use Generator;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class StreamModelResponse implements ModelResponseInterface
{
    public function __construct(
        private ResponseInterface $response,
        private Generator $generator,
    ) {
    }

    public function getContent(): Generator
    {
        yield from $this->generator;
    }

    public function getRawResponse(): ResponseInterface
    {
        return $this->response;
    }
}
