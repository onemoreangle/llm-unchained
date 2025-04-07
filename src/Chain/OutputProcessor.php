<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain;

interface OutputProcessor
{
    public function processOutput(Output $output): void;
}
