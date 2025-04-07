<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain;

use OneMoreAngle\LlmUnchained\Chain;

interface ChainAwareProcessor
{
    public function setChain(Chain $chain): void;
}
