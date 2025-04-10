<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\Azure\OpenAI;

use SensitiveParameter;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Whisper;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Whisper\File;
use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Platform\ModelClient;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

readonly class WhisperModelClient implements ModelClient
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        private string $baseUrl,
        private string $deployment,
        private string $apiVersion,
        #[SensitiveParameter] private string $apiKey,
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        Assert::notStartsWith($baseUrl, 'http://', 'The base URL must not contain the protocol.');
        Assert::notStartsWith($baseUrl, 'https://', 'The base URL must not contain the protocol.');
        Assert::stringNotEmpty($deployment, 'The deployment must not be empty.');
        Assert::stringNotEmpty($apiVersion, 'The API version must not be empty.');
        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');
    }

    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Whisper && $input instanceof File;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        assert($input instanceof File);

        $url = sprintf('https://%s/openai/deployments/%s/audio/translations', $this->baseUrl, $this->deployment);

        return $this->httpClient->request('POST', $url, [
            'headers' => [
                'api-key' => $this->apiKey,
                'Content-Type' => 'multipart/form-data',
            ],
            'query' => ['api-version' => $this->apiVersion],
            'body' => array_merge($options, $model->getOptions(), [
                'model' => $model->getVersion(),
                'file' => fopen($input->path, 'r'),
            ]),
        ]);
    }
}
