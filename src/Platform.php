<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained;

use Traversable;
use OneMoreAngle\LlmUnchained\Exception\RuntimeException;
use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Model\Response\AsyncModelResponse;
use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface;
use OneMoreAngle\LlmUnchained\Platform\ModelClient;
use OneMoreAngle\LlmUnchained\Platform\ResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final readonly class Platform implements PlatformInterface
{
    /**
     * @var ModelClient[]
     */
    private array $modelClients;

    /**
     * @var ResponseConverter[]
     */
    private array $responseConverter;

    /**
     * @param iterable<ModelClient>       $modelClients
     * @param iterable<ResponseConverter> $responseConverter
     */
    public function __construct(iterable $modelClients, iterable $responseConverter)
    {
        $this->modelClients = $modelClients instanceof Traversable ? iterator_to_array($modelClients) : $modelClients;
        $this->responseConverter = $responseConverter instanceof Traversable ? iterator_to_array($responseConverter) : $responseConverter;
    }

    public function request(Model $model, array|string|object $input, array $options = []): ModelResponseInterface
    {
        $options = array_merge($model->getOptions(), $options);

        $response = $this->doRequest($model, $input, $options);

        return $this->convertResponse($model, $input, $response, $options);
    }

    /**
     * @param array<mixed>|string|object $input
     * @param array<string, mixed>       $options
     */
    private function doRequest(Model $model, array|string|object $input, array $options = []): HttpResponse
    {
        foreach ($this->modelClients as $modelClient) {
            if ($modelClient->supports($model, $input)) {
                return $modelClient->request($model, $input, $options);
            }
        }

        throw new RuntimeException('No response factory registered for model "'.$model::class.'" with given input.');
    }

    /**
     * @param array<mixed>|string|object $input
     * @param array<string, mixed>       $options
     */
    private function convertResponse(Model $model, object|array|string $input, HttpResponse $response, array $options): ModelResponseInterface
    {
        foreach ($this->responseConverter as $responseConverter) {
            if ($responseConverter->supports($model, $input)) {
                return new AsyncModelResponse($responseConverter, $response, $options);
            }
        }

        throw new RuntimeException('No response converter registered for model "'.$model::class.'" with given input.');
    }
}
