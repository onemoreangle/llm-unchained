<?php

namespace OneMoreAngle\LlmUnchained\Platform;

interface RequestBodyProducer
{
    /**
     * @return array<string, mixed>
     */
    public function createBody(): array;
}
