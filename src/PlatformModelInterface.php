<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Model\LanguageModel;

interface PlatformModelInterface
{
    public function getModel(): LanguageModel;
    public function getPlatform(): PlatformInterface;
}
