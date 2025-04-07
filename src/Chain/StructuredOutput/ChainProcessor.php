<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\StructuredOutput;

use OneMoreAngle\LlmUnchained\Chain\Input;
use OneMoreAngle\LlmUnchained\Chain\InputProcessor;
use OneMoreAngle\LlmUnchained\Chain\Output;
use OneMoreAngle\LlmUnchained\Chain\OutputProcessor;
use OneMoreAngle\LlmUnchained\Exception\InvalidArgumentException;
use OneMoreAngle\LlmUnchained\Exception\MissingModelSupport;
use OneMoreAngle\LlmUnchained\Model\Response\StructuredResponse;
use Symfony\Component\Serializer\SerializerInterface;

final class ChainProcessor implements InputProcessor, OutputProcessor
{
    private string $outputStructure;

    public function __construct(
        private readonly ResponseFormatFactoryInterface $responseFormatFactory,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function processInput(Input $input): void
    {
        $options = $input->getOptions();

        if (!isset($options['output_structure'])) {
            return;
        }

        if (!$input->llm->supportsStructuredOutput()) {
            throw MissingModelSupport::forStructuredOutput($input->llm::class);
        }

        if (true === ($options['stream'] ?? false)) {
            throw new InvalidArgumentException('Streamed responses are not supported for structured output');
        }

        $options['response_format'] = $this->responseFormatFactory->create($options['output_structure']);

        $this->outputStructure = $options['output_structure'];
        unset($options['output_structure']);

        $input->setOptions($options);
    }

    public function processOutput(Output $output): void
    {
        $options = $output->options;

        if ($output->response instanceof StructuredResponse) {
            return;
        }

        if (!isset($options['response_format'])) {
            return;
        }

        if (!isset($this->outputStructure)) {
            $output->response = new StructuredResponse(json_decode($output->response->getContent(), true));

            return;
        }

        $output->response = new StructuredResponse(
            $this->serializer->deserialize($output->response->getContent(), $this->outputStructure, 'json')
        );
    }
}
