<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model;

interface EmbeddingsModel extends Model
{
    public function supportsMultipleInputs(): bool;
}
