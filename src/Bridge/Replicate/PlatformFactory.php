<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\Replicate;

use SensitiveParameter;
use OneMoreAngle\LlmUnchained\Platform;
use Symfony\Component\Clock\Clock;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PlatformFactory
{
    public static function create(
        #[SensitiveParameter]
        string $apiKey,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        return new Platform(
            [new LlamaModelClient(new Client($httpClient ?? HttpClient::create(), new Clock(), $apiKey))],
            [new LlamaResponseConverter()],
        );
    }
}
