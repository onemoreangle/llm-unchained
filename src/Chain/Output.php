<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain;

use OneMoreAngle\LlmUnchained\Model\LanguageModel;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBagInterface;
use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface;

class Output
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public readonly LanguageModel       $llm,
        public ModelResponseInterface       $response,
        public readonly MessageBagInterface $messages,
        public readonly array               $options,
    ) {
    }
}
