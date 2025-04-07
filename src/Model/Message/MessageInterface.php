<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Message;

use JsonSerializable;

interface MessageInterface extends JsonSerializable
{
    public function getRole(): Role;

    /**
     * @return array<mixed|string,mixed>
     */
    public function accept(MessageVisitor $visitor): array;
}
