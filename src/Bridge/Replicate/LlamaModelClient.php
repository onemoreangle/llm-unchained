<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\Replicate;

use OneMoreAngle\LlmUnchained\Bridge\Meta\Llama;
use OneMoreAngle\LlmUnchained\Bridge\Meta\LlamaPromptConverter;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBagInterface;
use OneMoreAngle\LlmUnchained\Model\Message\SystemMessage;
use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Platform\ModelClient;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class LlamaModelClient implements ModelClient
{
    public function __construct(
        private Client $client,
        private LlamaPromptConverter $promptConverter = new LlamaPromptConverter(),
    ) {
    }

    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Llama && $input instanceof MessageBagInterface;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        Assert::isInstanceOf($model, Llama::class);
        Assert::isInstanceOf($input, MessageBagInterface::class);

        return $this->client->request(sprintf('meta/meta-%s', $model->getVersion()), 'predictions', [
            'system' => $this->promptConverter->convertMessage($input->getSystemMessage() ?? new SystemMessage('')),
            'prompt' => $this->promptConverter->convertToPrompt($input->withoutSystemMessage()),
        ]);
    }
}
