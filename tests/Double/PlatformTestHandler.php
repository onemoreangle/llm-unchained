<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Double;

use OneMoreAngle\LlmUnchained\Document\Vector;
use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface;
use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface as LlmResponse;
use OneMoreAngle\LlmUnchained\Model\Response\VectorModelResponse;
use OneMoreAngle\LlmUnchained\Platform;
use OneMoreAngle\LlmUnchained\Platform\ModelClient;
use OneMoreAngle\LlmUnchained\Platform\ResponseConverter;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final class PlatformTestHandler implements ModelClient, ResponseConverter
{
    public int $createCalls = 0;

    public function __construct(
        private readonly ?ModelResponseInterface $create = null,
    ) {
    }

    public static function createPlatform(?ModelResponseInterface $create = null): Platform
    {
        $handler = new self($create);

        return new Platform([$handler], [$handler]);
    }

    public function supports(Model $model, object|array|string $input): bool
    {
        return true;
    }

    public function request(Model $model, object|array|string $input, array $options = []): HttpResponse
    {
        ++$this->createCalls;

        return new MockResponse();
    }

    public function convert(HttpResponse $response, array $options = []): LlmResponse
    {
        return $this->create ?? new VectorModelResponse($response, new Vector([1, 2, 3]));
    }
}
