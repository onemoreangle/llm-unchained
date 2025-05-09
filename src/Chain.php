<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained;

use Traversable;
use OneMoreAngle\LlmUnchained\Chain\ChainAwareProcessor;
use OneMoreAngle\LlmUnchained\Chain\Input;
use OneMoreAngle\LlmUnchained\Chain\InputProcessor;
use OneMoreAngle\LlmUnchained\Chain\Output;
use OneMoreAngle\LlmUnchained\Chain\OutputProcessor;
use OneMoreAngle\LlmUnchained\Exception\InvalidArgumentException;
use OneMoreAngle\LlmUnchained\Exception\MissingModelSupport;
use OneMoreAngle\LlmUnchained\Exception\RuntimeException;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBagInterface;
use OneMoreAngle\LlmUnchained\Model\Response\AsyncModelResponse;
use OneMoreAngle\LlmUnchained\Model\Response\ModelResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

readonly class Chain implements ChainInterface
{
    /**
     * @var InputProcessor[]
     */
    protected array $inputProcessors;

    /**
     * @var OutputProcessor[]
     */
    protected array $outputProcessors;

    /**
     * @param InputProcessor[]  $inputProcessors
     * @param OutputProcessor[] $outputProcessors
     */
    public function __construct(
        protected PlatformModelInterface $platformModel,
        iterable $inputProcessors = [],
        iterable $outputProcessors = [],
        protected LoggerInterface $logger = new NullLogger(),
    ) {
        $this->inputProcessors = $this->initializeProcessors($inputProcessors, InputProcessor::class);
        $this->outputProcessors = $this->initializeProcessors($outputProcessors, OutputProcessor::class);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function call(MessageBagInterface $messages, array $options = []): ModelResponseInterface
    {
        $input = new Input($this->platformModel->getModel(), $messages, $options);
        array_map(fn (InputProcessor $processor) => $processor->processInput($input), $this->inputProcessors);

        $llm = $input->llm;
        $messages = $input->messages;
        $options = $input->getOptions();

        if ($messages->containsAudio() && !$llm->supportsAudioInput()) {
            throw MissingModelSupport::forAudioInput($llm::class);
        }

        if ($messages->containsImage() && !$llm->supportsImageInput()) {
            throw MissingModelSupport::forImageInput($llm::class);
        }

        try {
            $response = $this->platformModel->getPlatform()->request($llm, $messages, $options);

            if ($response instanceof AsyncModelResponse) {
                $response = $response->unwrap();
            }
        } catch (ClientExceptionInterface $e) {
            $message = $e->getMessage();
            $content = $e->getResponse()->toArray(false);

            $this->logger->debug($message, $content);

            throw new InvalidArgumentException('' === $message ? 'Invalid request to model or platform' : $message, previous: $e);
        } catch (HttpExceptionInterface $e) {
            throw new RuntimeException('Failed to request model', previous: $e);
        }

        $output = new Output($llm, $response, $messages, $options);
        array_map(fn (OutputProcessor $processor) => $processor->processOutput($output), $this->outputProcessors);

        return $output->response;
    }

    /**
     * @param InputProcessor[]|OutputProcessor[] $processors
     * @param class-string                       $interface
     *
     * @return InputProcessor[]|OutputProcessor[]
     */
    private function initializeProcessors(iterable $processors, string $interface): array
    {
        foreach ($processors as $processor) {
            if (!$processor instanceof $interface) {
                throw new InvalidArgumentException(sprintf('Processor %s must implement %s interface.', $processor::class, $interface));
            }

            if ($processor instanceof ChainAwareProcessor) {
                $processor->setChain($this);
            }
        }

        return $processors instanceof Traversable ? iterator_to_array($processors) : $processors;
    }
}
