<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Bridge\Meta;

use OneMoreAngle\LlmUnchained\Exception\RuntimeException;
use OneMoreAngle\LlmUnchained\Model\Message\AssistantMessage;
use OneMoreAngle\LlmUnchained\Model\Message\Content\Image;
use OneMoreAngle\LlmUnchained\Model\Message\Content\Text;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBagInterface;
use OneMoreAngle\LlmUnchained\Model\Message\SystemMessage;
use OneMoreAngle\LlmUnchained\Model\Message\UserMessage;

class LlamaPromptConverter
{
    public function convertToPrompt(MessageBagInterface $messageBag): string
    {
        $messages = [];

        /** @var UserMessage|SystemMessage|AssistantMessage $message */
        foreach ($messageBag->getMessages() as $message) {
            $messages[] = self::convertMessage($message);
        }

        $messages = array_filter($messages, fn ($message) => '' !== $message);

        return trim(implode("\n\n", $messages))."\n\n".'<|start_header_id|>assistant<|end_header_id|>';
    }

    public function convertMessage(UserMessage|SystemMessage|AssistantMessage $message): string
    {
        if ($message instanceof SystemMessage) {
            return trim(<<<SYSTEM
                <|begin_of_text|><|start_header_id|>system<|end_header_id|>

                {$message->content}<|eot_id|>
                SYSTEM);
        }

        if ($message instanceof AssistantMessage) {
            if ('' === $message->content || null === $message->content) {
                return '';
            }

            return trim(<<<ASSISTANT
                <|start_header_id|>{$message->getRole()->value}<|end_header_id|>

                {$message->content}<|eot_id|>
                ASSISTANT);
        }

        // Handling of UserMessage
        $count = count($message->content);

        $contentParts = [];
        if ($count > 1) {
            foreach ($message->content as $value) {
                if ($value instanceof Text) {
                    $contentParts[] = $value->text;
                }

                if ($value instanceof Image) {
                    $contentParts[] = $value->url;
                }
            }
        } elseif (1 === $count) {
            $value = $message->content[0];
            if ($value instanceof Text) {
                $contentParts[] = $value->text;
            }

            if ($value instanceof Image) {
                $contentParts[] = $value->url;
            }
        } else {
            throw new RuntimeException('Unsupported message type.');
        }

        $content = implode("\n", $contentParts);

        return trim(<<<USER
            <|start_header_id|>{$message->getRole()->value}<|end_header_id|>

            {$content}<|eot_id|>
            USER);
    }
}
