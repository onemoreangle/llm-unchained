<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Response;

use OneMoreAngle\LlmUnchained\Exception\InvalidArgumentException;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class ToolCallModelResponse implements ModelResponseInterface
{
    /**
     * @var ToolCall[]
     */
    protected array $toolCalls;

    public function __construct(protected ResponseInterface $response, ToolCall ...$toolCalls)
    {
        if (0 === count($toolCalls)) {
            throw new InvalidArgumentException('Response must have at least one tool call.');
        }

        $this->toolCalls = $toolCalls;
    }

    /**
     * @return ToolCall[]
     */
    public function getContent(): array
    {
        return $this->toolCalls;
    }

    public function getRawResponse(): ResponseInterface
    {
        return $this->response;
    }
}
