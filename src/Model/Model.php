<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model;

interface Model
{
    public function getVersion(): string;

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array;
}
