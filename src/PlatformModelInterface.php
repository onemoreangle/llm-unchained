<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained;

use OneMoreAngle\LlmUnchained\Model\LanguageModel;

interface PlatformModelInterface
{
    public function getModel(): LanguageModel;
    public function getPlatform(): PlatformInterface;
}
