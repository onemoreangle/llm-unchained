<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\Ollama;

use OneMoreAngle\LlmUnchained\Bridge\Meta\Llama;
use OneMoreAngle\LlmUnchained\Exception\RuntimeException;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBagInterface;
use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface as LlmResponse;
use OneMoreAngle\LlmUnchained\Model\Response\TextModelResponse;
use OneMoreAngle\LlmUnchained\Platform\ModelClient;
use OneMoreAngle\LlmUnchained\Platform\ResponseConverter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class LlamaModelHandler implements ModelClient, ResponseConverter
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $hostUrl,
    ) {
    }

    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Llama && $input instanceof MessageBagInterface;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        return $this->httpClient->request('POST', sprintf('%s/api/chat', $this->hostUrl), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'model' => $model->getVersion(),
                'messages' => $input,
                'stream' => false,
            ],
        ]);
    }

    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        $data = $response->toArray();

        if (!isset($data['message'])) {
            throw new RuntimeException('Response does not contain message');
        }

        if (!isset($data['message']['content'])) {
            throw new RuntimeException('Message does not contain content');
        }

        return new TextModelResponse($response, $data['message']['content']);
    }
}
