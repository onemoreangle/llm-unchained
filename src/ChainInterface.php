<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained;

use OneMoreAngle\LlmUnchained\Model\Message\MessageBagInterface;
use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface;

interface ChainInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function call(MessageBagInterface $messages, array $options = []): ModelResponseInterface;
}
