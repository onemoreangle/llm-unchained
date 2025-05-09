<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Fixture\StructuredOutput;

use DateTimeInterface;

final class UserWithConstructor
{
    /**
     * @param string $name The name of the user in lowercase
     */
    public function __construct(
        public int $id,
        public string $name,
        public DateTimeInterface $createdAt,
        public bool $isActive,
        public ?int $age = null,
    ) {
    }
}
