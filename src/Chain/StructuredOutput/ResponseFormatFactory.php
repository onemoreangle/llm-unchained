<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\StructuredOutput;

use OneMoreAngle\LlmUnchained\Chain\JsonSchema\Factory;

use function Symfony\Component\String\u;

readonly class ResponseFormatFactory implements ResponseFormatFactoryInterface
{
    public function __construct(
        private Factory $schemaFactory = new Factory(),
    ) {
    }

    public function create(string $responseClass): array
    {
        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => u($responseClass)->afterLast('\\')->toString(),
                'schema' => $this->schemaFactory->buildProperties($responseClass),
                'strict' => true,
            ],
        ];
    }
}
