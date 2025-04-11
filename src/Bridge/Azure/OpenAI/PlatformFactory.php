<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\Azure\OpenAI;

use SensitiveParameter;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Embeddings;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\GPT\ResponseConverter;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Whisper;
use OneMoreAngle\LlmUnchained\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class PlatformFactory
{
    public static function create(
        string $baseUrl,
        string $deployment,
        string $apiVersion,
        #[SensitiveParameter]
        string $apiKey,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        $embeddingsResponseFactory = new EmbeddingsModelClient($httpClient, $baseUrl, $deployment, $apiVersion, $apiKey);
        $GPTResponseFactory = new GPTModelClient($httpClient, $baseUrl, $deployment, $apiVersion, $apiKey);
        $whisperResponseFactory = new WhisperModelClient($httpClient, $baseUrl, $deployment, $apiVersion, $apiKey);

        return new Platform(
            [$GPTResponseFactory, $embeddingsResponseFactory, $whisperResponseFactory],
            [new ResponseConverter(), new Embeddings\ResponseConverter(), new Whisper\ResponseConverter()],
        );
    }
}
