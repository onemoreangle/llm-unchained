<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

use Generator;

final readonly class StreamResponse implements ResponseInterface
{
    public function __construct(
        private Generator $generator,
    ) {
    }

    public function getContent(): Generator
    {
        yield from $this->generator;
    }
}
