<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message\Content;

interface Content extends \JsonSerializable
{
    /**
     * @return array<mixed|string,mixed>
     */
    public function accept(ContentVisitor $visitor): array;
}
