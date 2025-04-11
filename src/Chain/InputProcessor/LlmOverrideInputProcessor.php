<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Chain\InputProcessor;

use OneMoreAngle\LlmUnchained\Chain\Input;
use OneMoreAngle\LlmUnchained\Chain\InputProcessor;
use OneMoreAngle\LlmUnchained\Exception\InvalidArgumentException;
use OneMoreAngle\LlmUnchained\Model\LanguageModel;

class LlmOverrideInputProcessor implements InputProcessor
{
    public function processInput(Input $input): void
    {
        $options = $input->getOptions();

        if (!array_key_exists('llm', $options)) {
            return;
        }

        if (!$options['llm'] instanceof LanguageModel) {
            throw new InvalidArgumentException(sprintf('Option "llm" must be an instance of %s.', LanguageModel::class));
        }

        $input->llm = $options['llm'];
    }
}
