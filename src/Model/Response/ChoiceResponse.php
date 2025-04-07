<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Response;

use OneMoreAngle\LlmUnchained\Exception\InvalidArgumentException;

final readonly class ChoiceResponse implements ResponseInterface
{
    /**
     * @var Choice[]
     */
    private array $choices;

    public function __construct(Choice ...$choices)
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
}
