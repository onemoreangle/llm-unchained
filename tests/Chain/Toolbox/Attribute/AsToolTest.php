<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Chain\Toolbox\Attribute;

use OneMoreAngle\LlmUnchained\Chain\Toolbox\Attribute\AsTool;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AsTool::class)]
class AsToolTest extends TestCase
{
    #[Test]
    public function canBeConstructed(): void
    {
        $attribute = new AsTool(
            name: 'name',
            description: 'description',
        );

        self::assertSame('name', $attribute->name);
        self::assertSame('description', $attribute->description);
    }
}
