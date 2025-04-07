<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Fixture\StructuredOutput;

use DateTimeInterface;

final class User
{
    public int $id;
    /**
     * @var string The name of the user in lowercase
     */
    public string $name;
    public DateTimeInterface $createdAt;
    public bool $isActive;
    public ?int $age = null;
}
