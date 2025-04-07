<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Response;

final readonly class TextResponse implements ResponseInterface
{
    public function __construct(
        private string $content,
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
