<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\OpenAI;

use SensitiveParameter;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\DallE\ModelClient as DallEModelClient;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Embeddings\ModelClient as EmbeddingsModelClient;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Embeddings\ResponseConverter as EmbeddingsResponseConverter;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\GPT\ModelClient as GPTModelClient;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\GPT\ResponseConverter as GPTResponseConverter;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Whisper\ModelClient as WhisperModelClient;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Whisper\ResponseConverter as WhisperResponseConverter;
use OneMoreAngle\LlmUnchained\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PlatformFactory
{
    public static function create(
        #[SensitiveParameter]
        string $apiKey,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);

        $dallEModelClient = new DallEModelClient($httpClient, $apiKey);

        return new Platform(
            [
                new GPTModelClient($httpClient, $apiKey),
                new EmbeddingsModelClient($httpClient, $apiKey),
                $dallEModelClient,
                new WhisperModelClient($httpClient, $apiKey),
            ],
            [
                new GPTResponseConverter(),
                new EmbeddingsResponseConverter(),
                $dallEModelClient,
                new WhisperResponseConverter(),
            ],
        );
    }
}
