<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

interface MessageInterface extends \JsonSerializable
{
    public function getRole(): Role;

    /**
     * @return array<mixed|string,mixed>
     */
    public function accept(MessageVisitor $visitor): array;
}
