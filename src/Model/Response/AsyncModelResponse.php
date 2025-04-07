<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Response;

use OneMoreAngle\LlmUnchained\Platform\ResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

class AsyncModelResponse implements ModelResponseInterface
{
    protected bool $isConverted = false;
    protected ModelResponseInterface $convertedResponse;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        protected readonly ResponseConverter $responseConverter,
        protected readonly HttpResponse $response,
        protected readonly array $options = [],
    ) {
    }

    public function getContent(): string|iterable|object|null
    {
        return $this->unwrap()->getContent();
    }

    public function unwrap(): ModelResponseInterface
    {
        if (!$this->isConverted) {
            $this->convertedResponse = $this->responseConverter->convert($this->response, $this->options);
            $this->isConverted = true;
        }

        return $this->convertedResponse;
    }

    public function getRawResponse(): HttpResponse
    {
        return $this->response;
    }
}
