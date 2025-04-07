<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Chain\StructuredOutput;

use OneMoreAngle\LlmUnchained\Chain\JsonSchema\DescriptionParser;
use OneMoreAngle\LlmUnchained\Chain\JsonSchema\Factory;
use OneMoreAngle\LlmUnchained\Chain\StructuredOutput\ResponseFormatFactory;
use OneMoreAngle\LlmUnchained\Tests\Fixture\StructuredOutput\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResponseFormatFactory::class)]
#[UsesClass(DescriptionParser::class)]
#[UsesClass(Factory::class)]
final class ResponseFormatFactoryTest extends TestCase
{
    #[Test]
    public function create(): void
    {
        self::assertSame([
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'User',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => [
                            'type' => 'string',
                            'description' => 'The name of the user in lowercase',
                        ],
                        'createdAt' => [
                            'type' => 'string',
                            'format' => 'date-time',
                        ],
                        'isActive' => ['type' => 'boolean'],
                        'age' => ['type' => ['integer', 'null']],
                    ],
                    'required' => ['id', 'name', 'createdAt', 'isActive'],
                    'additionalProperties' => false,
                ],
                'strict' => true,
            ],
        ], (new ResponseFormatFactory())->create(User::class));
    }
}
