<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\OpenRouter;

use SensitiveParameter;
use OneMoreAngle\LlmUnchained\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PlatformFactory
{
    public static function create(
        #[SensitiveParameter]
        string $apiKey,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        $handler = new Client($httpClient, $apiKey);

        return new Platform([$handler], [$handler]);
    }
}
