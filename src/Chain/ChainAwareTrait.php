<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain;

use OneMoreAngle\LlmUnchained\Chain;

trait ChainAwareTrait
{
    private Chain $chain;

    public function setChain(Chain $chain): void
    {
        $this->chain = $chain;
    }
}
