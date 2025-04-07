<?php

namespace OneMoreAngle\LlmUnchained\Model\Message\Content;

interface ContentVisitor
{
    /**
     * @return array<mixed|string,mixed>
     */
    public function visitAudio(Audio $content): array;

    /**
     * @return array<mixed|string,mixed>
     */
    public function visitImage(Image $content): array;

    /**
     * @return array<mixed|string,mixed>
     */
    public function visitText(Text $content): array;
}
