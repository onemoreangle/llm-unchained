<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Bridge\OpenAI\DallE;

use OneMoreAngle\LlmUnchained\Bridge\OpenAI\DallE\Base64Image;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\DallE\ImageModelResponse;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\DallE\UrlImage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(ImageModelResponse::class)]
#[UsesClass(Base64Image::class)]
#[UsesClass(UrlImage::class)]
#[Small]
final class ImageResponseTest extends TestCase
{
    #[Test]
    public function itCreatesImagesResponse(): void
    {
        $base64Image = new Base64Image('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $responseMock = $this->createMock(ResponseInterface::class);
        $generatedImagesResponse = new ImageModelResponse($responseMock, null, $base64Image);

        self::assertNull($generatedImagesResponse->revisedPrompt);
        self::assertCount(1, $generatedImagesResponse->getContent());
        self::assertSame($base64Image, $generatedImagesResponse->getContent()[0]);
    }

    #[Test]
    public function itCreatesImagesResponseWithRevisedPrompt(): void
    {
        $base64Image = new Base64Image('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $responseMock = $this->createMock(ResponseInterface::class);
        $generatedImagesResponse = new ImageModelResponse($responseMock, 'revised prompt', $base64Image);

        self::assertSame('revised prompt', $generatedImagesResponse->revisedPrompt);
        self::assertCount(1, $generatedImagesResponse->getContent());
        self::assertSame($base64Image, $generatedImagesResponse->getContent()[0]);
    }

    #[Test]
    public function itIsCreatableWithMultipleImages(): void
    {
        $image1 = new UrlImage('https://example');
        $image2 = new UrlImage('https://example2');

        $responseMock = $this->createMock(ResponseInterface::class);
        $generatedImagesResponse = new ImageModelResponse($responseMock, null, $image1, $image2);

        self::assertCount(2, $generatedImagesResponse->getContent());
        self::assertSame($image1, $generatedImagesResponse->getContent()[0]);
        self::assertSame($image2, $generatedImagesResponse->getContent()[1]);
    }
}
