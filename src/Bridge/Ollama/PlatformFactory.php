<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\Ollama;

use OneMoreAngle\LlmUnchained\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PlatformFactory
{
    public static function create(
        string $hostUrl = 'http://localhost:11434',
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        $handler = new LlamaModelHandler($httpClient, $hostUrl);

        return new Platform([$handler], [$handler]);
    }
}
