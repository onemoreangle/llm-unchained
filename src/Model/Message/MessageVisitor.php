<?php

namespace PhpLlm\LlmChain\Model\Message;

interface MessageVisitor
{
    /**
     * @return array<mixed|string,mixed>
     */
    public function visitSystemMessage(SystemMessage $message): array;

    /**
     * @return array<mixed|string,mixed>
     */
    public function visitUserMessage(UserMessage $message): array;

    /**
     * @return array<mixed|string,mixed>
     */
    public function visitAssistantMessage(AssistantMessage $message): array;

    /**
     * @return array<mixed|string,mixed>
     */
    public function visitToolCallMessage(ToolCallMessage $message): array;
}
