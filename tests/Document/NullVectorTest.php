<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Document;

use OneMoreAngle\LlmUnchained\Document\NullVector;
use OneMoreAngle\LlmUnchained\Document\VectorInterface;
use OneMoreAngle\LlmUnchained\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(NullVector::class)]
final class NullVectorTest extends TestCase
{
    #[Test]
    public function implementsInterface(): void
    {
        self::assertInstanceOf(VectorInterface::class, new NullVector());
    }

    #[Test]
    public function getDataThrowsOnAccess(): void
    {
        $this->expectException(RuntimeException::class);

        (new NullVector())->getData();
    }

    #[Test]
    public function getDimensionsThrowsOnAccess(): void
    {
        $this->expectException(RuntimeException::class);

        (new NullVector())->getDimensions();
    }
}
