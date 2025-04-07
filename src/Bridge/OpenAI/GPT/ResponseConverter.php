<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\OpenAI\GPT;

use Generator;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\GPT;
use OneMoreAngle\LlmUnchained\Exception\ContentFilterException;
use OneMoreAngle\LlmUnchained\Exception\RuntimeException;
use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Model\Response\Choice;
use OneMoreAngle\LlmUnchained\Model\Response\ChoiceModelResponse;
use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface as LlmResponse;
use OneMoreAngle\LlmUnchained\Model\Response\StreamModelResponse;
use OneMoreAngle\LlmUnchained\Model\Response\TextModelResponse;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCall;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCallModelResponse;
use OneMoreAngle\LlmUnchained\Platform\ResponseConverter as PlatformResponseConverter;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final class ResponseConverter implements PlatformResponseConverter
{
    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof GPT;
    }

    public function convert(HttpResponse $response, array $options = []): LlmResponse
    {
        if ($options['stream'] ?? false) {
            return new StreamModelResponse($response, $this->convertStream($response));
        }

        try {
            $data = $response->toArray();
        } catch (ClientExceptionInterface $e) {
            $data = $response->toArray(throw: false);

            if (isset($data['error']['code']) && 'content_filter' === $data['error']['code']) {
                throw new ContentFilterException(message: $data['error']['message'], previous: $e);
            }

            throw $e;
        }

        if (!isset($data['choices'])) {
            throw new RuntimeException('Response does not contain choices');
        }

        /** @var Choice[] $choices */
        $choices = \array_map($this->convertChoice(...), $data['choices']);

        if (1 !== count($choices)) {
            return new ChoiceModelResponse($response, ...$choices);
        }

        if ($choices[0]->hasToolCall()) {
            return new ToolCallModelResponse($response, ...$choices[0]->getToolCalls());
        }

        return new TextModelResponse($response, $choices[0]->getContent());
    }

    private function convertStream(HttpResponse $response): Generator
    {
        $toolCalls = [];
        foreach ((new EventSourceHttpClient())->stream($response) as $chunk) {
            if (!$chunk instanceof ServerSentEvent || '[DONE]' === $chunk->getData()) {
                continue;
            }

            try {
                $data = $chunk->getArrayData();
            } catch (JsonException) {
                // try catch only needed for Symfony 6.4
                continue;
            }

            if ($this->streamIsToolCall($data)) {
                $toolCalls = $this->convertStreamToToolCalls($toolCalls, $data);
            }

            if ([] !== $toolCalls && $this->isToolCallsStreamFinished($data)) {
                yield new ToolCallModelResponse($response, ...\array_map($this->convertToolCall(...), $toolCalls));
            }

            if (!isset($data['choices'][0]['delta']['content'])) {
                continue;
            }

            yield $data['choices'][0]['delta']['content'];
        }
    }

    /**
     * @param array<string, mixed> $toolCalls
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function convertStreamToToolCalls(array $toolCalls, array $data): array
    {
        if (!isset($data['choices'][0]['delta']['tool_calls'])) {
            return $toolCalls;
        }

        foreach ($data['choices'][0]['delta']['tool_calls'] as $i => $toolCall) {
            if (isset($toolCall['id'])) {
                // initialize tool call
                $toolCalls[$i] = [
                    'id' => $toolCall['id'],
                    'function' => $toolCall['function'],
                ];
                continue;
            }

            // add arguments delta to tool call
            $toolCalls[$i]['function']['arguments'] .= $toolCall['function']['arguments'];
        }

        return $toolCalls;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function streamIsToolCall(array $data): bool
    {
        return isset($data['choices'][0]['delta']['tool_calls']);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function isToolCallsStreamFinished(array $data): bool
    {
        return isset($data['choices'][0]['finish_reason']) && 'tool_calls' === $data['choices'][0]['finish_reason'];
    }

    /**
     * @param array{
     *     index: integer,
     *     message: array{
     *         role: 'assistant',
     *         content: ?string,
     *         tool_calls: array{
     *             id: string,
     *             type: 'function',
     *             function: array{
     *                 name: string,
     *                 arguments: string
     *             },
     *         },
     *         refusal: ?mixed
     *     },
     *     logprobs: string,
     *     finish_reason: 'stop'|'length'|'tool_calls'|'content_filter',
     * } $choice
     */
    private function convertChoice(array $choice): Choice
    {
        if ('tool_calls' === $choice['finish_reason']) {
            return new Choice(toolCalls: \array_map([$this, 'convertToolCall'], $choice['message']['tool_calls']));
        }

        if (in_array($choice['finish_reason'], ['stop', 'length'], true)) {
            return new Choice($choice['message']['content']);
        }

        throw new RuntimeException(sprintf('Unsupported finish reason "%s".', $choice['finish_reason']));
    }

    /**
     * @param array{
     *     id: string,
     *     type: 'function',
     *     function: array{
     *         name: string,
     *         arguments: string
     *     }
     * } $toolCall
     */
    private function convertToolCall(array $toolCall): ToolCall
    {
        $arguments = json_decode($toolCall['function']['arguments'], true, JSON_THROW_ON_ERROR);

        return new ToolCall($toolCall['id'], $toolCall['function']['name'], $arguments);
    }
}
