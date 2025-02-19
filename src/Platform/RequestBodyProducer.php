<?php

namespace PhpLlm\LlmChain\Platform;

interface RequestBodyProducer
{
    /**
     * @return array<string, mixed>
     */
    public function createBody(): array;
}
