<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain;

interface InputProcessor
{
    public function processInput(Input $input): void;
}
