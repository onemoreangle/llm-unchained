<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox;

use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolMetadataException;

interface MetadataFactory
{
    /**
     * @return iterable<Metadata>
     *
     * @throws ToolMetadataException if the metadata for the given reference is not found
     */
    public function getMetadata(string $reference): iterable;
}
