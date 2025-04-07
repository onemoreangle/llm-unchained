<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox;

use Generator;
use Closure;
use OneMoreAngle\LlmUnchained\Model\Message\Message;
use OneMoreAngle\LlmUnchained\Model\Response\ResponseInterface;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCallResponse;

final readonly class StreamResponse implements ResponseInterface
{
    public function __construct(
        private Generator $generator,
        private Closure $handleToolCallsCallback,
    ) {
    }

    public function getContent(): Generator
    {
        $streamedResponse = '';
        foreach ($this->generator as $value) {
            if ($value instanceof ToolCallResponse) {
                yield from ($this->handleToolCallsCallback)($value, Message::ofAssistant($streamedResponse))->getContent();

                break;
            }

            $streamedResponse .= $value;
            yield $value;
        }
    }
}
