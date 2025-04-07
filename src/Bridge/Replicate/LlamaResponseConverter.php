<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\Replicate;

use OneMoreAngle\LlmUnchained\Bridge\Meta\Llama;
use OneMoreAngle\LlmUnchained\Exception\RuntimeException;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBagInterface;
use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface as LlmResponse;
use OneMoreAngle\LlmUnchained\Model\Response\TextModelResponse;
use OneMoreAngle\LlmUnchained\Platform\ResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final readonly class LlamaResponseConverter implements ResponseConverter
{
    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Llama && $input instanceof MessageBagInterface;
    }

    public function convert(HttpResponse $response, array $options = []): LlmResponse
    {
        $data = $response->toArray();

        if (!isset($data['output'])) {
            throw new RuntimeException('Response does not contain output');
        }

        return new TextModelResponse($response, implode('', $data['output']));
    }
}
