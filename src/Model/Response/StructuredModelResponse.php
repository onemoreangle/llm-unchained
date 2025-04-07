<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Response;

use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class StructuredModelResponse implements ModelResponseInterface
{
    /**
     * @param object|array<string, mixed> $structuredOutput
     */
    public function __construct(
        protected ResponseInterface $response,
        private object|array $structuredOutput,
    ) {
    }

    /**
     * @return object|array<string, mixed>
     */
    public function getContent(): object|array
    {
        return $this->structuredOutput;
    }

    public function getRawResponse(): ResponseInterface
    {
        return $this->response;
    }
}
