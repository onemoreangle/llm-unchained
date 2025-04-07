<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox;

use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolExecutionException;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Exception\ToolNotFoundException;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCall;

/**
 * Catches exceptions thrown by the inner tool box and returns error messages for the LLM instead.
 */
final readonly class FaultTolerantToolbox implements ToolboxInterface
{
    public function __construct(
        private ToolboxInterface $innerToolbox,
    ) {
    }

    public function getMap(): array
    {
        return $this->innerToolbox->getMap();
    }

    public function execute(ToolCall $toolCall): mixed
    {
        try {
            return $this->innerToolbox->execute($toolCall);
        } catch (ToolExecutionException $e) {
            return sprintf('An error occurred while executing tool "%s".', $e->toolCall->name);
        } catch (ToolNotFoundException) {
            $names = array_map(fn (Metadata $metadata) => $metadata->name, $this->getMap());

            return sprintf('Tool "%s" was not found, please use one of these: %s', $toolCall->name, implode(', ', $names));
        }
    }
}
