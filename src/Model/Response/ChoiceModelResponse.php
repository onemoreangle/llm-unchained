<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Response;

use OneMoreAngle\LlmUnchained\Exception\InvalidArgumentException;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class ChoiceModelResponse implements ModelResponseInterface
{
    /**
     * @var Choice[]
     */
    private array $choices;

    public function __construct(protected ResponseInterface $response, Choice ...$choices)
    {
        if (0 === count($choices)) {
            throw new InvalidArgumentException('Response must have at least one choice.');
        }

        $this->choices = $choices;
    }

    /**
     * @return Choice[]
     */
    public function getContent(): array
    {
        return $this->choices;
    }

    public function getRawResponse(): ResponseInterface
    {
        return $this->response;
    }
}
