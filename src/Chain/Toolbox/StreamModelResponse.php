<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox;

use Generator;
use Closure;
use OneMoreAngle\LlmUnchained\Model\Message\Message;
use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface;
use OneMoreAngle\LlmUnchained\Model\Response\StreamModelResponse as GenericStreamResponse;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCallModelResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class StreamModelResponse implements ModelResponseInterface
{
    protected Generator $generator;

    public function __construct(
        protected GenericStreamResponse $response,
        protected Closure $handleToolCallsCallback,
    ) {
        $this->generator = $this->response->getContent();
    }

    public function getContent(): Generator
    {
        $streamedResponse = '';
        foreach ($this->generator as $value) {
            if ($value instanceof ToolCallModelResponse) {
                yield from ($this->handleToolCallsCallback)($value, Message::ofAssistant($streamedResponse))->getContent();

                break;
            }

            $streamedResponse .= $value;
            yield $value;
        }
    }

    public function getRawResponse(): ResponseInterface
    {
        return $this->response->getRawResponse();
    }
}
