<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\Toolbox;

use Closure;
use OneMoreAngle\LlmUnchained\Chain\ChainAwareProcessor;
use OneMoreAngle\LlmUnchained\Chain\ChainAwareTrait;
use OneMoreAngle\LlmUnchained\Chain\Input;
use OneMoreAngle\LlmUnchained\Chain\InputProcessor;
use OneMoreAngle\LlmUnchained\Chain\Output;
use OneMoreAngle\LlmUnchained\Chain\OutputProcessor;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Event\ToolCallsExecuted;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\StreamModelResponse as ToolboxStreamResponse;
use OneMoreAngle\LlmUnchained\Exception\MissingModelSupport;
use OneMoreAngle\LlmUnchained\Model\Message\AssistantMessage;
use OneMoreAngle\LlmUnchained\Model\Message\Message;
use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface;
use OneMoreAngle\LlmUnchained\Model\Response\StreamModelResponse as GenericStreamResponse;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCallModelResponse;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ChainProcessor implements InputProcessor, OutputProcessor, ChainAwareProcessor
{
    use ChainAwareTrait;

    public function __construct(
        private readonly ToolboxInterface $toolbox,
        private readonly ToolResultConverter $resultConverter = new ToolResultConverter(),
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    public function processInput(Input $input): void
    {
        if (!$input->llm->supportsToolCalling()) {
            throw MissingModelSupport::forToolCalling($input->llm::class);
        }

        $toolMap = $this->toolbox->getMap();
        if ([] === $toolMap) {
            return;
        }

        $options = $input->getOptions();
        // only filter tool map if list of strings is provided as option
        if (isset($options['tools']) && $this->isFlatStringArray($options['tools'])) {
            $toolMap = array_values(array_filter($toolMap, fn (Metadata $tool) => in_array($tool->name, $options['tools'], true)));
        }

        $options['tools'] = $toolMap;
        $input->setOptions($options);
    }

    public function processOutput(Output $output): void
    {
        if ($output->response instanceof GenericStreamResponse) {
            $output->response = new ToolboxStreamResponse(
                $output->response,
                $this->handleToolCallsCallback($output),
            );

            return;
        }

        if (!$output->response instanceof ToolCallModelResponse) {
            return;
        }

        $output->response = $this->handleToolCallsCallback($output)($output->response);
    }

    /**
     * @param array<mixed> $tools
     */
    private function isFlatStringArray(array $tools): bool
    {
        return array_reduce($tools, fn (bool $carry, mixed $item) => $carry && is_string($item), true);
    }

    private function handleToolCallsCallback(Output $output): Closure
    {
        return function (ToolCallModelResponse $response, ?AssistantMessage $streamedAssistantResponse = null) use ($output): ModelResponseInterface {
            $messages = clone $output->messages;

            if (null !== $streamedAssistantResponse && '' !== $streamedAssistantResponse->content) {
                $messages->add($streamedAssistantResponse);
            }

            do {
                $toolCalls = $response->getContent();
                $messages->add(Message::ofAssistant(toolCalls: $toolCalls));

                $results = [];
                foreach ($toolCalls as $toolCall) {
                    $result = $this->toolbox->execute($toolCall);
                    $results[] = new ToolCallResult($toolCall, $result);
                    $messages->add(Message::ofToolCall($toolCall, $this->resultConverter->convert($result)));
                }

                $event = new ToolCallsExecuted(...$results);
                $this->eventDispatcher?->dispatch($event);

                $response = $event->hasResponse() ? $event->response : $this->chain->call($messages, $output->options);
            } while ($response instanceof ToolCallModelResponse);

            return $response;
        };
    }
}
