<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Store;

interface InitializableStoreInterface extends StoreInterface
{
    /**
     * @param array<mixed> $options
     */
    public function initialize(array $options = []): void;
}
