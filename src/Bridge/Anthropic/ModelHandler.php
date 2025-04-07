<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\Anthropic;

use SensitiveParameter;
use Generator;
use OneMoreAngle\LlmUnchained\Exception\RuntimeException;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBagInterface;
use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Model\Response\ResponseInterface as LlmResponse;
use OneMoreAngle\LlmUnchained\Model\Response\StreamResponse;
use OneMoreAngle\LlmUnchained\Model\Response\TextResponse;
use OneMoreAngle\LlmUnchained\Platform\ModelClient;
use OneMoreAngle\LlmUnchained\Platform\ResponseConverter;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class ModelHandler implements ModelClient, ResponseConverter
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[SensitiveParameter] private string $apiKey,
        private string $version = '2023-06-01',
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
    }

    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof Claude && $input instanceof MessageBagInterface;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        Assert::isInstanceOf($input, MessageBagInterface::class);

        $system = $input->getSystemMessage();
        $body = array_merge($options, [
            'model' => $model->getVersion(),
            'system' => $system->content,
            'messages' => $input->withoutSystemMessage(),
        ]);

        return $this->httpClient->request('POST', 'https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => $this->version,
            ],
            'json' => $body,
        ]);
    }

    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        if ($options['stream'] ?? false) {
            return new StreamResponse($this->convertStream($response));
        }

        $data = $response->toArray();

        if (!isset($data['content']) || 0 === count($data['content'])) {
            throw new RuntimeException('Response does not contain any content');
        }

        if (!isset($data['content'][0]['text'])) {
            throw new RuntimeException('Response content does not contain any text');
        }

        return new TextResponse($data['content'][0]['text']);
    }

    private function convertStream(ResponseInterface $response): Generator
    {
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

            if ('content_block_delta' != $data['type'] || !isset($data['delta']['text'])) {
                continue;
            }

            yield $data['delta']['text'];
        }
    }
}
