<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\OpenAI\DallE;

use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ImageModelResponse implements ModelResponseInterface
{
    /** @var list<Base64Image|UrlImage> */
    private readonly array $images;

    public function __construct(
        protected ResponseInterface $response,
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

    public function getRawResponse(): ResponseInterface
    {
        return $this->response;
    }
}
