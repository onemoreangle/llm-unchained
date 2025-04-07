<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Bridge\OpenAI\DallE;

use InvalidArgumentException;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\DallE\UrlImage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UrlImage::class)]
#[Small]
final class UrlImageTest extends TestCase
{
    #[Test]
    public function itCreatesUrlImage(): void
    {
        $urlImage = new UrlImage('https://example.com/image.jpg');

        self::assertSame('https://example.com/image.jpg', $urlImage->url);
    }

    #[Test]
    public function itThrowsExceptionWhenUrlIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The image url must be given.');

        new UrlImage('');
    }
}
