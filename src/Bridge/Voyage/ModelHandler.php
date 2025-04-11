<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\Voyage;

use SensitiveParameter;
use OneMoreAngle\LlmUnchained\Document\Vector;
use OneMoreAngle\LlmUnchained\Exception\RuntimeException;
use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface as LlmResponse;
use OneMoreAngle\LlmUnchained\Model\Response\VectorModelResponse;
use OneMoreAngle\LlmUnchained\Platform\ModelClient;
use OneMoreAngle\LlmUnchained\Platform\ResponseConverter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class ModelHandler implements ModelClient, ResponseConverter
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[SensitiveParameter] private string $apiKey,
    ) {
    }

    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof Voyage;
    }

    public function request(Model $model, object|string|array $input, array $options = []): ResponseInterface
    {
        return $this->httpClient->request('POST', 'https://api.voyageai.com/v1/embeddings', [
            'auth_bearer' => $this->apiKey,
            'json' => [
                'model' => $model->getVersion(),
                'input' => $input,
            ],
        ]);
    }

    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        $result = $response->toArray();

        if (!isset($response['data'])) {
            throw new RuntimeException('Response does not contain embedding data');
        }

        $vectors = array_map(fn (array $data) => new Vector($data['embedding']), $result['data']);

        return new VectorModelResponse($response, $vectors[0]);
    }
}
