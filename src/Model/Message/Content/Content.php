<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Message\Content;

use JsonSerializable;

interface Content extends JsonSerializable
{
    /**
     * @return array<mixed|string,mixed>
     */
    public function accept(ContentVisitor $visitor): array;
}
