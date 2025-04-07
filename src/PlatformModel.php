<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Model\LanguageModel;

class PlatformModel implements PlatformModelInterface
{
    public function __construct(
        protected readonly PlatformInterface $platform,
        protected readonly LanguageModel $llm,
    ) {
    }

    public function getModel(): LanguageModel
    {
        return $this->llm;
    }
    public function getPlatform(): PlatformInterface
    {
        return $this->platform;
    }
}
