<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\OpenRouter;

use OneMoreAngle\LlmUnchained\Bridge\Meta\Llama;
use OneMoreAngle\LlmUnchained\Model\LanguageModel;

readonly class GenericModel implements LanguageModel
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private string $version = Llama::LLAMA_3_2_90B_VISION_INSTRUCT,
        private array $options = [],
    ) {
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function supportsAudioInput(): bool
    {
        return false; // it does, but implementation here is still open.
    }

    public function supportsImageInput(): bool
    {
        return false; // it does, but implementation here is still open.
    }

    public function supportsStreaming(): bool
    {
        return false; // it does, but implementation here is still open.
    }

    public function supportsToolCalling(): bool
    {
        return false; // it does, but implementation here is still open.
    }

    public function supportsStructuredOutput(): bool
    {
        return false; // it does, but implementation here is still open.
    }
}
