<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Model\Message;

use OneMoreAngle\LlmUnchained\Model\Message\Content\Audio;
use OneMoreAngle\LlmUnchained\Model\Message\Content\Content;
use OneMoreAngle\LlmUnchained\Model\Message\Content\Image;
use OneMoreAngle\LlmUnchained\Model\Message\Content\Text;

readonly class UserMessage implements MessageInterface
{
    /**
     * @var list<Content>
     */
    public array $content;

    public function __construct(
        Content ...$content,
    ) {
        $this->content = $content;
    }

    public function getRole(): Role
    {
        return Role::User;
    }

    public function hasAudioContent(): bool
    {
        foreach ($this->content as $content) {
            if ($content instanceof Audio) {
                return true;
            }
        }

        return false;
    }

    public function hasImageContent(): bool
    {
        foreach ($this->content as $content) {
            if ($content instanceof Image) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{
     *     role: Role::User,
     *     content: string|list<Content>
     * }
     */
    public function jsonSerialize(): array
    {
        $array = ['role' => Role::User];
        if (1 === count($this->content) && $this->content[0] instanceof Text) {
            $array['content'] = $this->content[0]->text;

            return $array;
        }

        $array['content'] = $this->content;

        return $array;
    }

    /**
     * @return array<mixed|string,mixed>
     */
    public function accept(MessageVisitor $visitor): array
    {
        return $visitor->visitUserMessage($this);
    }
}
