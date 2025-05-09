<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox;

use JsonSerializable;
use OneMoreAngle\LlmUnchained\Chain\JsonSchema\Factory;

/**
 * @phpstan-import-type JsonSchema from Factory
 */
readonly class Metadata implements JsonSerializable
{
    /**
     * @param JsonSchema|null $parameters
     */
    public function __construct(
        public ExecutionReference $reference,
        public string $name,
        public string $description,
        public ?array $parameters,
    ) {
    }

    /**
     * @return array{
     *     type: 'function',
     *     function: array{
     *         name: string,
     *         description: string,
     *         parameters?: JsonSchema
     *     }
     * }
     */
    public function jsonSerialize(): array
    {
        $function = [
            'name' => $this->name,
            'description' => $this->description,
        ];

        if (isset($this->parameters)) {
            $function['parameters'] = $this->parameters;
        }

        return [
            'type' => 'function',
            'function' => $function,
        ];
    }
}
