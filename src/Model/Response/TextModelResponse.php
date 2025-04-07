<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Response;

use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class TextModelResponse implements ModelResponseInterface
{
    public function __construct(
        protected ResponseInterface $response,
        protected string $content,
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getRawResponse(): ResponseInterface
    {
        return $this->response;
    }
}
