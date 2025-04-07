<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\OpenAI\DallE;

use OneMoreAngle\LlmUnchained\Model\Response\ResponseInterface;

class ImageResponse implements ResponseInterface
{
    /** @var list<Base64Image|UrlImage> */
    private readonly array $images;

    public function __construct(
        public ?string $revisedPrompt = null, // Only string on Dall-E 3 usage
        Base64Image|UrlImage ...$images,
    ) {
        $this->images = \array_values($images);
    }

    /**
     * @return list<Base64Image|UrlImage>
     */
    public function getContent(): array
    {
        return $this->images;
    }
}
