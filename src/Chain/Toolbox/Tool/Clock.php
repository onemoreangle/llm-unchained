<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox\Tool;

use OneMoreAngle\LlmUnchained\Chain\Toolbox\Attribute\AsTool;
use Symfony\Component\Clock\Clock as SymfonyClock;
use Symfony\Component\Clock\ClockInterface;

#[AsTool('clock', description: 'Provides the current date and time.')]
readonly class Clock
{
    public function __construct(
        private ClockInterface $clock = new SymfonyClock(),
    ) {
    }

    public function __invoke(): string
    {
        return sprintf(
            'Current date is %s (YYYY-MM-DD) and the time is %s (HH:MM:SS).',
            $this->clock->now()->format('Y-m-d'),
            $this->clock->now()->format('H:i:s'),
        );
    }
}
