<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\OpenAI\Embeddings;

use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Embeddings;
use OneMoreAngle\LlmUnchained\Document\Vector;
use OneMoreAngle\LlmUnchained\Exception\RuntimeException;
use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Model\Response\VectorModelResponse;
use OneMoreAngle\LlmUnchained\Platform\ResponseConverter as PlatformResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ResponseConverter implements PlatformResponseConverter
{
    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof Embeddings;
    }

    public function convert(ResponseInterface $response, array $options = []): VectorModelResponse
    {
        $data = $response->toArray();

        if (!isset($data['data'])) {
            throw new RuntimeException('Response does not contain data');
        }

        return new VectorModelResponse(
            $response,
            ...\array_map(
                static fn (array $item): Vector => new Vector($item['embedding']),
                $data['data']
            ),
        );
    }
}
