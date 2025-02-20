<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Google;

use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\StreamResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;
use PhpLlm\LlmChain\Platform\ModelClient;
use PhpLlm\LlmChain\Platform\ResponseConverter;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class ModelHandler implements ModelClient, ResponseConverter
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[\SensitiveParameter] private string $apiKey,
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
    }

    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof GoogleModel && $input instanceof MessageBagInterface;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        Assert::isInstanceOf($input, MessageBagInterface::class);

        $body = new GoogleRequestBodyProducer($input, $options);

        return $this->httpClient->request('POST', sprintf('https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent', $model->getVersion()), [
            'headers' => [
                'x-goog-api-key' => $this->apiKey,
            ],
            'json' => $body,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        if ($options['stream'] ?? false) {
            return new StreamResponse($this->convertStream($response));
        }

        $data = $response->toArray();

        if (!isset($data['candidates'][0])) {
            throw new RuntimeException('Response does not contain any candidates');
        }

        $candidate = $data['candidates'][0];

        if (isset($candidate['content']['parts'][0]['functionCall'])) {
            $toolCalls = [];

            foreach ($candidate['content']['parts'] as $part) {
                if (!isset($part['functionCall'])) {
                    continue;
                }

                $toolCalls[] = new ToolCall(
                    id: uniqid('google-'),
                    name: $part['functionCall']['name'],
                    arguments: (array) $part['functionCall']['args']
                );
            }

            return new ToolCallResponse(...$toolCalls);
        }

        // Regular text response
        if (isset($candidate['content']['parts'][0]['text'])) {
            return new TextResponse($candidate['content']['parts'][0]['text']);
        }

        throw new RuntimeException('Response format not supported');
    }

    private function convertStream(ResponseInterface $response): \Generator
    {
        foreach ($this->httpClient->stream($response) as $chunk) {
            if (!$chunk instanceof ServerSentEvent || '[DONE]' === $chunk->getData()) {
                continue;
            }

            try {
                $data = $chunk->getArrayData();
            } catch (JsonException) {
                continue;
            }

            if (!isset($data['candidates'][0]['content']['parts'][0])) {
                continue;
            }

            $part = $data['candidates'][0]['content']['parts'][0];

            if (isset($part['functionCall'])) {
                $toolCall = new ToolCall(
                    id: uniqid('google-'),
                    name: $part['functionCall']['name'],
                    arguments: (array) $part['functionCall']['args']
                );

                yield new ToolCallResponse($toolCall);
                continue;
            }

            if (isset($part['text'])) {
                yield $part['text'];
            }
        }
    }
}
