<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Response;

use OneMoreAngle\LlmUnchained\Document\Vector;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class VectorModelResponse implements ModelResponseInterface
{
    /**
     * @var Vector[]
     */
    protected array $vectors;

    public function __construct(protected ResponseInterface $response, Vector ...$vector)
    {
        $this->vectors = $vector;
    }

    /**
     * @return Vector[]
     */
    public function getContent(): array
    {
        return $this->vectors;
    }

    public function getRawResponse(): ResponseInterface
    {
        return $this->response;
    }
}
