<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\OpenAI\Whisper;

use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Whisper;
use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface as LlmResponse;
use OneMoreAngle\LlmUnchained\Model\Response\TextModelResponse;
use OneMoreAngle\LlmUnchained\Platform\ResponseConverter as BaseResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

class ResponseConverter implements BaseResponseConverter
{
    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Whisper && $input instanceof File;
    }

    public function convert(HttpResponse $response, array $options = []): LlmResponse
    {
        $data = $response->toArray();

        return new TextModelResponse($response, $data['text']);
    }
}
