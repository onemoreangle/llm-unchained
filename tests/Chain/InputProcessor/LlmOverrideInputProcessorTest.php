<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Chain\InputProcessor;

use OneMoreAngle\LlmUnchained\Bridge\Anthropic\Claude;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Embeddings;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\GPT;
use OneMoreAngle\LlmUnchained\Chain\Input;
use OneMoreAngle\LlmUnchained\Chain\InputProcessor\LlmOverrideInputProcessor;
use OneMoreAngle\LlmUnchained\Exception\InvalidArgumentException;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LlmOverrideInputProcessor::class)]
#[UsesClass(GPT::class)]
#[UsesClass(Claude::class)]
#[UsesClass(Input::class)]
#[UsesClass(MessageBag::class)]
#[UsesClass(Embeddings::class)]
#[Small]
final class LlmOverrideInputProcessorTest extends TestCase
{
    #[Test]
    public function processInputWithValidLlmOption(): void
    {
        $gpt = new GPT();
        $claude = new Claude();
        $input = new Input($gpt, new MessageBag(), ['llm' => $claude]);

        $processor = new LlmOverrideInputProcessor();
        $processor->processInput($input);

        self::assertSame($claude, $input->llm);
    }

    #[Test]
    public function processInputWithoutLlmOption(): void
    {
        $gpt = new GPT();
        $input = new Input($gpt, new MessageBag(), []);

        $processor = new LlmOverrideInputProcessor();
        $processor->processInput($input);

        self::assertSame($gpt, $input->llm);
    }

    #[Test]
    public function processInputWithInvalidLlmOption(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Option "llm" must be an instance of OneMoreAngle\LlmUnchained\Model\LanguageModel.');

        $gpt = new GPT();
        $model = new Embeddings();
        $input = new Input($gpt, new MessageBag(), ['llm' => $model]);

        $processor = new LlmOverrideInputProcessor();
        $processor->processInput($input);
    }
}
