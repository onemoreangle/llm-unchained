<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Chain\StructuredOutput;

use OneMoreAngle\LlmUnchained\Chain\Input;
use OneMoreAngle\LlmUnchained\Chain\Output;
use OneMoreAngle\LlmUnchained\Chain\StructuredOutput\ChainProcessor;
use OneMoreAngle\LlmUnchained\Exception\MissingModelSupport;
use OneMoreAngle\LlmUnchained\Model\LanguageModel;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBag;
use OneMoreAngle\LlmUnchained\Model\Response\Choice;
use OneMoreAngle\LlmUnchained\Model\Response\StructuredModelResponse;
use OneMoreAngle\LlmUnchained\Model\Response\TextModelResponse;
use OneMoreAngle\LlmUnchained\Tests\Double\ConfigurableResponseFormatFactory;
use OneMoreAngle\LlmUnchained\Tests\Fixture\SomeStructure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(ChainProcessor::class)]
#[UsesClass(Input::class)]
#[UsesClass(Output::class)]
#[UsesClass(MessageBag::class)]
#[UsesClass(Choice::class)]
#[UsesClass(MissingModelSupport::class)]
#[UsesClass(TextModelResponse::class)]
#[UsesClass(StructuredModelResponse::class)]
final class ChainProcessorTest extends TestCase
{
    #[Test]
    public function processInputWithOutputStructure(): void
    {
        $responseFormatFactory = new ConfigurableResponseFormatFactory(['some' => 'format']);
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $chainProcessor = new ChainProcessor($responseFormatFactory, $serializer);

        $llm = $this->createMock(LanguageModel::class);
        $llm->method('supportsStructuredOutput')->willReturn(true);

        $input = new Input($llm, new MessageBag(), ['output_structure' => 'SomeStructure']);

        $chainProcessor->processInput($input);

        self::assertSame(['response_format' => ['some' => 'format']], $input->getOptions());
    }

    #[Test]
    public function processInputWithoutOutputStructure(): void
    {
        $responseFormatFactory = new ConfigurableResponseFormatFactory();
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $chainProcessor = new ChainProcessor($responseFormatFactory, $serializer);

        $llm = $this->createMock(LanguageModel::class);
        $input = new Input($llm, new MessageBag(), []);

        $chainProcessor->processInput($input);

        self::assertSame([], $input->getOptions());
    }

    #[Test]
    public function processInputThrowsExceptionWhenLlmDoesNotSupportStructuredOutput(): void
    {
        $this->expectException(MissingModelSupport::class);

        $responseFormatFactory = new ConfigurableResponseFormatFactory();
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $chainProcessor = new ChainProcessor($responseFormatFactory, $serializer);

        $llm = $this->createMock(LanguageModel::class);
        $llm->method('supportsStructuredOutput')->willReturn(false);

        $input = new Input($llm, new MessageBag(), ['output_structure' => 'SomeStructure']);

        $chainProcessor->processInput($input);
    }

    #[Test]
    public function processOutputWithResponseFormat(): void
    {
        $responseFormatFactory = new ConfigurableResponseFormatFactory(['some' => 'format']);
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $chainProcessor = new ChainProcessor($responseFormatFactory, $serializer);

        $llm = $this->createMock(LanguageModel::class);
        $llm->method('supportsStructuredOutput')->willReturn(true);

        $options = ['output_structure' => SomeStructure::class];
        $input = new Input($llm, new MessageBag(), $options);
        $chainProcessor->processInput($input);

        $responseMock = $this->createMock(ResponseInterface::class);
        $response = new TextModelResponse($responseMock, '{"some": "data"}');

        $output = new Output($llm, $response, new MessageBag(), $input->getOptions());

        $chainProcessor->processOutput($output);

        self::assertInstanceOf(StructuredModelResponse::class, $output->response);
        self::assertInstanceOf(SomeStructure::class, $output->response->getContent());
        self::assertSame('data', $output->response->getContent()->some);
    }

    #[Test]
    public function processOutputWithoutResponseFormat(): void
    {
        $responseFormatFactory = new ConfigurableResponseFormatFactory();
        $serializer = $this->createMock(SerializerInterface::class);
        $chainProcessor = new ChainProcessor($responseFormatFactory, $serializer);

        $llm = $this->createMock(LanguageModel::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $response = new TextModelResponse($responseMock, '');

        $output = new Output($llm, $response, new MessageBag(), []);

        $chainProcessor->processOutput($output);

        self::assertSame($response, $output->response);
    }
}
