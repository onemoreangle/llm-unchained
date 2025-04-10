<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model;

interface LanguageModel extends Model
{
    public function supportsAudioInput(): bool;

    public function supportsImageInput(): bool;

    public function supportsStreaming(): bool;

    public function supportsStructuredOutput(): bool;

    public function supportsToolCalling(): bool;
}
