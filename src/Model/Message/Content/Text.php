<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Message\Content;

final readonly class Text implements Content
{
    public function __construct(
        public string $text,
    ) {
    }

    /**
     * @return array{type: 'text', text: string}
     */
    public function jsonSerialize(): array
    {
        return ['type' => 'text', 'text' => $this->text];
    }

    /**
     * @return array<mixed|string,mixed>
     */
    public function accept(ContentVisitor $visitor): array
    {
        return $visitor->visitText($this);
    }
}
