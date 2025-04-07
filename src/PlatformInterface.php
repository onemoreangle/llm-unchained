<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained;

use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Model\Response\ResponseInterface;

interface PlatformInterface
{
    /**
     * @param array<mixed>|string|object $input
     * @param array<string, mixed>       $options
     */
    public function request(Model $model, array|string|object $input, array $options = []): ResponseInterface;
}
