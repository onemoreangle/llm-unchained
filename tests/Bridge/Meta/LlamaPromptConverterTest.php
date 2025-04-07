<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Bridge\Meta;

use OneMoreAngle\LlmUnchained\Bridge\Meta\LlamaPromptConverter;
use OneMoreAngle\LlmUnchained\Model\Message\AssistantMessage;
use OneMoreAngle\LlmUnchained\Model\Message\Content\Image;
use OneMoreAngle\LlmUnchained\Model\Message\Content\Text;
use OneMoreAngle\LlmUnchained\Model\Message\Message;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBag;
use OneMoreAngle\LlmUnchained\Model\Message\SystemMessage;
use OneMoreAngle\LlmUnchained\Model\Message\UserMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LlamaPromptConverter::class)]
#[Small]
#[UsesClass(AssistantMessage::class)]
#[UsesClass(Image::class)]
#[UsesClass(Text::class)]
#[UsesClass(Message::class)]
#[UsesClass(MessageBag::class)]
#[UsesClass(SystemMessage::class)]
#[UsesClass(UserMessage::class)]
final class LlamaPromptConverterTest extends TestCase
{
    #[Test]
    public function convertMessages(): void
    {
        $messageBag = new MessageBag();
        foreach (self::provideMessages() as $message) {
            $messageBag->add($message[1]);
        }

        self::assertSame(
            <<<EXPECTED
                <|begin_of_text|><|start_header_id|>system<|end_header_id|>

                You are a helpful chatbot.<|eot_id|>

                <|start_header_id|>user<|end_header_id|>

                Hello, how are you?<|eot_id|>

                <|start_header_id|>user<|end_header_id|>

                Hello, how are you?
                What is your name?<|eot_id|>

                <|start_header_id|>user<|end_header_id|>

                Hello, how are you?
                What is your name?
                https://example.com/image.jpg<|eot_id|>

                <|start_header_id|>assistant<|end_header_id|>

                I am an assistant.<|eot_id|>

                <|start_header_id|>assistant<|end_header_id|>
                EXPECTED,
            (new LlamaPromptConverter())->convertToPrompt($messageBag)
        );
    }

    #[Test]
    #[DataProvider('provideMessages')]
    public function convertMessage(string $expected, UserMessage|SystemMessage|AssistantMessage $message): void
    {
        self::assertSame(
            $expected,
            (new LlamaPromptConverter())->convertMessage($message)
        );
    }

    /**
     * @return iterable<array{string, UserMessage|SystemMessage|AssistantMessage}>
     */
    public static function provideMessages(): iterable
    {
        yield 'System message' => [
            <<<SYSTEM
                <|begin_of_text|><|start_header_id|>system<|end_header_id|>

                You are a helpful chatbot.<|eot_id|>
                SYSTEM,

            Message::forSystem('You are a helpful chatbot.'),
        ];

        yield 'UserMessage' => [
            <<<USER
                <|start_header_id|>user<|end_header_id|>

                Hello, how are you?<|eot_id|>
                USER,
            Message::ofUser('Hello, how are you?'),
        ];

        yield 'UserMessage with two texts' => [
            <<<USER
                <|start_header_id|>user<|end_header_id|>

                Hello, how are you?
                What is your name?<|eot_id|>
                USER,
            Message::ofUser('Hello, how are you?', 'What is your name?'),
        ];

        yield 'UserMessage with two texts and one image' => [
            <<<USER
                <|start_header_id|>user<|end_header_id|>

                Hello, how are you?
                What is your name?
                https://example.com/image.jpg<|eot_id|>
                USER,
            Message::ofUser('Hello, how are you?', 'What is your name?', new Image('https://example.com/image.jpg')),
        ];

        yield 'AssistantMessage' => [
            <<<ASSISTANT
                <|start_header_id|>assistant<|end_header_id|>

                I am an assistant.<|eot_id|>
                ASSISTANT,
            new AssistantMessage('I am an assistant.'),
        ];

        yield 'AssistantMessage with null content' => [
            '',
            new AssistantMessage(),
        ];
    }
}
