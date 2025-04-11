<?php

namespace OneMoreAngle\LlmUnchained\Bridge\Google;

use JsonSerializable;
use InvalidArgumentException;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Metadata;
use OneMoreAngle\LlmUnchained\Exception\MissingModelSupport;
use OneMoreAngle\LlmUnchained\Model\Message\AssistantMessage;
use OneMoreAngle\LlmUnchained\Model\Message\Content\Audio;
use OneMoreAngle\LlmUnchained\Model\Message\Content\ContentVisitor;
use OneMoreAngle\LlmUnchained\Model\Message\Content\Image;
use OneMoreAngle\LlmUnchained\Model\Message\Content\Text;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBagInterface;
use OneMoreAngle\LlmUnchained\Model\Message\MessageVisitor;
use OneMoreAngle\LlmUnchained\Model\Message\Role;
use OneMoreAngle\LlmUnchained\Model\Message\SystemMessage;
use OneMoreAngle\LlmUnchained\Model\Message\ToolCallMessage;
use OneMoreAngle\LlmUnchained\Model\Message\UserMessage;
use OneMoreAngle\LlmUnchained\Model\Model;
use OneMoreAngle\LlmUnchained\Model\Response\ToolCall;
use OneMoreAngle\LlmUnchained\Platform\RequestBodyProducer;

use function Symfony\Component\String\u;

class GoogleRequestBodyProducer implements RequestBodyProducer, MessageVisitor, ContentVisitor, JsonSerializable
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(protected MessageBagInterface $bag, protected array $options = [], protected ?Model $model = null)
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

        if ($generationConfig = $this->getGenerationConfig()) {
            $body['generationConfig'] = $generationConfig;
        }

        return $body;
    }

    /**
     * @return array<string, mixed>
     */
    private function getGenerationConfig(): array
    {
        $generationConfig = [];

        if (isset($this->options['temperature'])) {
            $generationConfig['temperature'] = $this->options['temperature'];
        }

        if (isset($this->options['topP'])) {
            $generationConfig['topP'] = $this->options['topP'];
        }

        if (isset($this->options['topK'])) {
            $generationConfig['topK'] = $this->options['topK'];
        }

        if (isset($this->options['maxOutputTokens'])) {
            $generationConfig['maxOutputTokens'] = $this->options['maxOutputTokens'];
        }

        if (isset($this->options['stopSequences'])) {
            $generationConfig['stopSequences'] = $this->options['stopSequences'];
        }

        if (isset($this->options['response_format'])) {
            $response_format = $this->options['response_format'];
            if ('json_schema' !== $response_format['type']) {
                throw MissingModelSupport::forStructuredOutput(GoogleModel::class);
            }

            $generationConfig['response_mime_type'] = 'application/json';
            $generationConfig['response_schema'] = $response_format['json_schema']['schema'];
        }

        return $generationConfig;
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
        if (str_starts_with($this->model->getVersion(), 'gemma-')) {
            throw new InvalidArgumentException('Gemma models do not support system instructions');
        }

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
     * @return array<string, mixed>
     */
    public function visitImage(Image $content): array
    {
        if (str_starts_with($content->url, 'data:')) {
            $type = u($content->url)->after('data:')->before(';')->toString();
            $data = u($content->url)->after('base64,')->toString();
        } else {
            $type = pathinfo($content->url, PATHINFO_EXTENSION);
            $type = 'jpg' !== $type ? "image/{$type}" : 'image/jpeg';
            $data = base64_encode(file_get_contents($content->url));
        }

        return ['inline_data' => [
            'mime_type' => $type,
            'data' => $data,
        ]];
    }

    /**
     * @return array<string, mixed>
     */
    public function visitAudio(Audio $content): array
    {
        return ['inline_data' => [
            'mime_type' => "audio/{$content->format}",
            'data' => $content->data,
        ]];
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
