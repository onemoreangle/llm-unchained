<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\Google;

use OneMoreAngle\LlmUnchained\Model\LanguageModel;

readonly class GoogleModel implements LanguageModel
{
    public const GEMINI_3_1_PRO_PREVIEW = 'gemini-3.1-pro-preview';
    public const GEMINI_3_FLASH_PREVIEW = 'gemini-3-flash-preview';
    public const GEMINI_3_1_FLASH_LITE_PREVIEW = 'gemini-3.1-flash-lite-preview';

    public const GEMINI_2_5_PRO = 'gemini-2.5-pro';
    public const GEMINI_2_5_FLASH = 'gemini-2.5-flash';
    public const GEMINI_2_5_FLASH_LITE = 'gemini-2.5-flash-lite';

    public const GEMMA_3_27B = 'gemma-3-27b-it';
    public const GEMMA_3_12B = 'gemma-3-12b-it';
    public const GEMMA_3_4B = 'gemma-3-4b-it';
    public const GEMMA_3_1B = 'gemma-3-1b-it';

    /**
     * @param array<string, mixed> $options The default options for the model usage
     */
    public function __construct(
        private string $version = self::GEMINI_2_5_FLASH,
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
        return !$this->isGemma();
    }

    public function supportsImageInput(): bool
    {
        return self::GEMMA_3_1B !== $this->version;
    }

    public function supportsStreaming(): bool
    {
        return true;
    }

    public function supportsStructuredOutput(): bool
    {
        return !$this->isGemma();
    }

    public function supportsToolCalling(): bool
    {
        return !$this->isGemma();
    }

    private function isGemma(): bool
    {
        return str_starts_with($this->version, 'gemma-');
    }
}
