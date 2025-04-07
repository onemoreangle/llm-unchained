<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Double;

use OneMoreAngle\LlmUnchained\Chain\StructuredOutput\ResponseFormatFactoryInterface;

final readonly class ConfigurableResponseFormatFactory implements ResponseFormatFactoryInterface
{
    /**
     * @param array<mixed> $responseFormat
     */
    public function __construct(
        private array $responseFormat = [],
    ) {
    }

    public function create(string $responseClass): array
    {
        return $this->responseFormat;
    }
}
