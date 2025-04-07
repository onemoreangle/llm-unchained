<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\Google;

use OneMoreAngle\LlmUnchained\Chain\JsonSchema\DescriptionParser;
use OneMoreAngle\LlmUnchained\Chain\JsonSchema\Factory;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;

/**
 * Factory for creating Gemini API compatible schemas.
 * Overrides the standard JSON Schema factory to produce Gemini-compatible schemas.
 *
 * @phpstan-type GeminiSchema array{
 *     type: string,
 *     properties?: array<string, array{
 *         type: string,
 *         description?: string,
 *         format?: string,
 *         enum?: list<string>,
 *         minItems?: int,
 *         maxItems?: int,
 *         items?: array<string, mixed>,
 *         properties?: array<string, mixed>,
 *         required?: list<string>,
 *         propertyOrdering?: list<string>
 *     }>,
 *     items?: array<string, mixed>,
 *     required?: list<string>,
 *     propertyOrdering?: list<string>,
 * }
 */
readonly class GeminiSchemaFactory extends Factory
{
    public function __construct(
        DescriptionParser $descriptionParser = new DescriptionParser(),
        ?TypeResolver $typeResolver = null,
    ) {
        parent::__construct($descriptionParser, $typeResolver);
    }

    /**
     * @return GeminiSchema|null
     *
     * @override
     */
    public function buildParameters(string $className, string $methodName): ?array
    {
        $jsonSchema = parent::buildParameters($className, $methodName);

        return $this->transformToGeminiSchema(is_array($jsonSchema) ? $jsonSchema : null);
    }

    /**
     * @return GeminiSchema|null
     *
     * @override
     */
    public function buildProperties(string $className): ?array
    {
        $jsonSchema = parent::buildProperties($className);

        return $this->transformToGeminiSchema(is_array($jsonSchema) ? $jsonSchema : null);
    }

    /**
     * Transform standard JSON Schema to Gemini schema format.
     *
     * @param array<string, mixed>|null $schema
     *
     * @return array<string, mixed>|null
     */
    private function transformToGeminiSchema(?array $schema): ?array
    {
        if (null === $schema || !isset($schema['type'])) {
            return null;
        }

        $result = [
            'type' => $this->convertType(is_string($schema['type']) || is_array($schema['type']) ? $schema['type'] : 'string'),
        ];

        if (isset($schema['properties']) && is_array($schema['properties'])) {
            $result['properties'] = [];
            $propertyNames = [];

            foreach ($schema['properties'] as $propertyName => $propertySchema) {
                $propertyNames[] = $propertyName;
                if (is_array($propertySchema)) {
                    $result['properties'][$propertyName] = $this->transformPropertySchema($propertySchema);
                }
            }

            if (!empty($propertyNames)) {
                $result['propertyOrdering'] = $propertyNames;
            }
        }

        if (isset($schema['required']) && is_array($schema['required'])) {
            $result['required'] = array_values(array_filter($schema['required'], 'is_string'));
        }

        if (isset($schema['items']) && is_array($schema['items'])) {
            $result['items'] = $this->transformToGeminiSchema($schema['items']);
        }

        /* @var GeminiSchema $result */
        return $result;
    }

    /**
     * Transform a property schema to Gemini format.
     *
     * @param array<string, mixed> $propertySchema
     *
     * @return array<string, mixed>
     */
    private function transformPropertySchema(array $propertySchema): array
    {
        $result = [
            'type' => $this->convertType(isset($propertySchema['type']) && (is_string($propertySchema['type']) || is_array($propertySchema['type'])) ? $propertySchema['type'] : 'string'),
        ];

        $supportedAttributes = [
            'description' => 'description',
            'format' => 'format',
            'enum' => 'enum',
            'minItems' => 'minItems',
            'maxItems' => 'maxItems',
        ];

        foreach ($supportedAttributes as $sourceKey => $targetKey) {
            if (isset($propertySchema[$sourceKey])) {
                $result[$targetKey] = $propertySchema[$sourceKey];
            }
        }
        if (isset($result['enum']) && is_array($result['enum'])) {
            $result['enum'] = array_values(array_filter($result['enum'], 'is_string'));
        }

        if (isset($propertySchema['properties']) && is_array($propertySchema['properties'])) {
            $nestedSchema = [
                'type' => isset($propertySchema['type']) && (is_string($propertySchema['type']) || is_array($propertySchema['type'])) ? $propertySchema['type'] : 'object',
                'properties' => $propertySchema['properties'],
            ];

            if (isset($propertySchema['required']) && is_array($propertySchema['required'])) {
                $nestedSchema['required'] = array_values(array_filter($propertySchema['required'], 'is_string'));
            }

            $transformedNested = $this->transformToGeminiSchema($nestedSchema);
            if (is_array($transformedNested)) {
                $result = array_merge($result, $transformedNested);
            }
        } elseif (isset($propertySchema['items']) && is_array($propertySchema['items'])) {
            $transformedItems = $this->transformToGeminiSchema($propertySchema['items']);
            if (is_array($transformedItems)) {
                $result['items'] = $transformedItems;
            }
        }

        return $result;
    }

    /**
     * Convert JSON Schema type to Gemini type.
     *
     * @param string|array<int, string> $type
     */
    private function convertType(string|array $type): string
    {
        if (is_array($type)) {
            foreach ($type as $t) {
                if ('null' !== $t && is_string($t)) {
                    return $this->convertType($t);
                }
            }

            return 'STRING';
        }

        return match ($type) {
            'object' => 'OBJECT',
            'array' => 'ARRAY',
            'integer' => 'INTEGER',
            'number' => 'NUMBER',
            'boolean' => 'BOOLEAN',
            default => 'STRING',
        };
    }
}
