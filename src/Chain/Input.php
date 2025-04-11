<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain;

use OneMoreAngle\LlmUnchained\Model\LanguageModel;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBagInterface;

class Input
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public LanguageModel $llm,
        public MessageBagInterface $messages,
        private array $options,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
