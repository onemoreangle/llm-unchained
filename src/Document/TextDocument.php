<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Document;

use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

readonly class TextDocument
{
    public function __construct(
        public Uuid $id,
        public string $content,
        public Metadata $metadata = new Metadata(),
    ) {
        Assert::stringNotEmpty(trim($this->content));
    }
}
