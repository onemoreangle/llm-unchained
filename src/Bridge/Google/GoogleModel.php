<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Google;

use PhpLlm\LlmChain\Model\LanguageModel;

final readonly class GoogleModel implements LanguageModel
{
    public const GEMINI_2_5_PREVIEW = 'gemini-2.5-pro-preview-03-25';
    public const GEMINI_2_FLASH = 'gemini-2.0-flash';
    public const GEMINI_2_PRO = 'gemini-2.0-pro-exp-02-05';
    public const GEMINI_2_FLASH_LITE = 'gemini-2.0-flash-lite-preview-02-05';
    public const GEMINI_2_FLASH_THINKING = 'gemini-2.0-flash-thinking-exp-01-21';
    public const GEMINI_1_5_FLASH = 'gemini-1.5-flash';
    public const GEMMA_3_27B = 'gemma-3-27b-it';
    public const GEMMA_3_12B = 'gemma-3-12b-it';
    public const GEMMA_3_4B = 'gemma-3-4b-it';
    public const GEMMA_3_1B = 'gemma-3-1b-it';
    public const GEMMA_2_27B = 'gemma-2-27b-it';
    public const GEMMA_2_9B = 'gemma-2-9b-it';
    public const GEMMA_2_2B = 'gemma-2-2b-it';

    /**
     * @param array<string, mixed> $options The default options for the model usage
     */
    public function __construct(
        private string $version = self::GEMINI_2_PRO,
        private array $options = ['temperature' => 1.0],
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
        return !in_array($this->version, [self::GEMINI_2_FLASH_THINKING, self::GEMMA_2_2B, self::GEMMA_2_9B, self::GEMMA_2_27B, self::GEMMA_3_1B, self::GEMMA_3_4B, self::GEMMA_3_12B, self::GEMMA_3_27B], true);
    }

    public function supportsImageInput(): bool
    {
        return !in_array($this->version, [self::GEMMA_2_2B, self::GEMMA_2_9B, self::GEMMA_2_27B], true);
    }

    public function supportsStreaming(): bool
    {
        return true;
    }

    public function supportsStructuredOutput(): bool
    {
        return true;
    }

    public function supportsToolCalling(): bool
    {
        return true;
    }
}
