<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\OpenAI\Whisper;

use SensitiveParameter;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Whisper;
use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Platform\ModelClient as BaseModelClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

readonly class ModelClient implements BaseModelClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[SensitiveParameter]
        private string $apiKey,
    ) {
        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');
    }

    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Whisper && $input instanceof File;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        assert($input instanceof File);

        return $this->httpClient->request('POST', 'https://api.openai.com/v1/audio/transcriptions', [
            'auth_bearer' => $this->apiKey,
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'body' => array_merge($options, $model->getOptions(), [
                'model' => $model->getVersion(),
                'file' => fopen($input->path, 'r'),
            ]),
        ]);
    }
}
