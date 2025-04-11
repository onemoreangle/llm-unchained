<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox;

class ExecutionReference
{
    public function __construct(
        public string $class,
        public string $method = '__invoke',
    ) {
    }
}
