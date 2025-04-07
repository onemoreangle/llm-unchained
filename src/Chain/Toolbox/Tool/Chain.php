<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox\Tool;

use OneMoreAngle\LlmUnchained\ChainInterface;
use OneMoreAngle\LlmUnchained\Model\Message\Message;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBag;
use OneMoreAngle\LlmUnchained\Model\Response\TextResponse;

final readonly class Chain
{
    public function __construct(
        private ChainInterface $chain,
    ) {
    }

    /**
     * @param string $message the message to pass to the chain
     */
    public function __invoke(string $message): string
    {
        $response = $this->chain->call(new MessageBag(Message::ofUser($message)));

        assert($response instanceof TextResponse);

        return $response->getContent();
    }
}
