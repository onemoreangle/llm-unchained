<?php

namespace PhpLlm\LlmChain\Bridge\Google;

use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\Content\Audio;
use PhpLlm\LlmChain\Model\Message\Content\ContentVisitor;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Message\MessageVisitor;
use PhpLlm\LlmChain\Model\Message\Role;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PhpLlm\LlmChain\Model\Message\ToolCallMessage;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Platform\RequestBodyProducer;

final class GoogleRequestBodyProducer implements RequestBodyProducer, MessageVisitor, ContentVisitor, \JsonSerializable
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(protected MessageBagInterface $bag, protected array $options = [])
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function createBody(): array
    {
        $contents = [];
        foreach ($this->bag->withoutSystemMessage()->getMessages() as $message) {
            $contents[] = $message->accept($this);
        }

        $body = [
            'contents' => $contents,
        ];

        $systemMessage = $this->bag->getSystemMessage();
        if (null !== $systemMessage) {
            $body['systemInstruction'] = $systemMessage->accept($this);
        }

        if (!empty($this->options['tools'])) {
            $body['tools'] = [
                [
                    'function_declarations' => array_map(
                        fn ($metadata) => $this->metadata($metadata),
                        $this->options['tools']
                    ),
                ],
            ];
        }

        return $body;
    }

    /**
     * @return array<string, mixed>
     */
    private function metadata(Metadata $metadata): array
    {
        $declaration = [
            'name' => $metadata->name,
            'description' => $metadata->description,
        ];

        if (null !== $metadata->parameters) {
            $declaration['parameters'] = [
                'type' => 'object',
                'properties' => $metadata->parameters['properties'],
            ];

            if (!empty($metadata->parameters['required'])) {
                $declaration['parameters']['required'] = $metadata->parameters['required'];
            }
        }

        return $declaration;
    }

    /**
     * @return array<string, mixed>
     */
    public function visitUserMessage(UserMessage $message): array
    {
        $parts = [];
        foreach ($message->content as $content) {
            $parts[] = $content->accept($this);
        }

        return [
            'role' => $message->getRole(),
            'parts' => $parts,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function visitAssistantMessage(AssistantMessage $message): array
    {
        if ($message->toolCalls) {
            return [
                'role' => 'model',
                'parts' => array_map(
                    fn (ToolCall $toolCall) => [
                        'functionCall' => [
                            'name' => $toolCall->name,
                            'args' => (object) $toolCall->arguments,
                        ],
                    ],
                    $message->toolCalls
                ),
            ];
        }

        return [
            'role' => $message->getRole(),
            'parts' => [['text' => $message->content]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function visitSystemMessage(SystemMessage $message): array
    {
        return [
            'role' => $message->getRole(),
            'parts' => [['text' => $message->content]],
        ];
    }

    /**
     * @return string[]
     */
    public function visitText(Text $content): array
    {
        return ['text' => $content->text];
    }

    /**
     * @return string[]
     */
    public function visitImage(Image $content): array
    {
        // TODO: support image
        return [];
    }

    /**
     * @return string[]
     */
    public function visitAudio(Audio $content): array
    {
        // TODO: support audio
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function visitToolCallMessage(ToolCallMessage $message): array
    {
        return [
            'role' => Role::User,
            'parts' => [
                [
                    'functionResponse' => [
                        'name' => $message->toolCall->name,
                        'response' => [
                            'name' => $message->toolCall->name,
                            'content' => $message->content,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->createBody();
    }
}
