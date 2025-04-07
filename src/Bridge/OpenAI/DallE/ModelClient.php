<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\OpenAI\DallE;

use SensitiveParameter;
use RuntimeException;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\DallE;
use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface as LlmResponse;
use OneMoreAngle\LlmUnchained\Platform\ModelClient as PlatformResponseFactory;
use OneMoreAngle\LlmUnchained\Platform\ResponseConverter as PlatformResponseConverter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;
use Webmozart\Assert\Assert;

/**
 * @see https://platform.openai.com/docs/api-reference/images/create
 */
final readonly class ModelClient implements PlatformResponseFactory, PlatformResponseConverter
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[SensitiveParameter]
        private string $apiKey,
    ) {
        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');
        Assert::startsWith($apiKey, 'sk-', 'The API key must start with "sk-".');
    }

    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof DallE;
    }

    public function request(Model $model, object|array|string $input, array $options = []): HttpResponse
    {
        return $this->httpClient->request('POST', 'https://api.openai.com/v1/images/generations', [
            'auth_bearer' => $this->apiKey,
            'json' => \array_merge($options, [
                'model' => $model->getVersion(),
                'prompt' => $input,
            ]),
        ]);
    }

    public function convert(HttpResponse $response, array $options = []): LlmResponse
    {
        $result = $response->toArray();
        if (!isset($result['data'][0])) {
            throw new RuntimeException('No image generated.');
        }

        $images = [];
        foreach ($result['data'] as $image) {
            if ('url' === $options['response_format']) {
                $images[] = new UrlImage($image['url']);

                continue;
            }

            $images[] = new Base64Image($image['b64_json']);
        }

        return new ImageModelResponse($response, $image['revised_prompt'] ?? null, ...$images);
    }
}
