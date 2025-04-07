<?php

use OneMoreAngle\LlmUnchained\PlatformModel;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\GPT;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\PlatformFactory;
use OneMoreAngle\LlmUnchained\Chain;
use OneMoreAngle\LlmUnchained\Model\Message\Message;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

if (empty($_ENV['RUN_EXPENSIVE_EXAMPLES']) || false === filter_var($_ENV['RUN_EXPENSIVE_EXAMPLES'], FILTER_VALIDATE_BOOLEAN)) {
    echo 'This example is marked as expensive and will not run unless RUN_EXPENSIVE_EXAMPLES is set to true.'.PHP_EOL;
    exit(134);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$llm = new GPT(GPT::O1_PREVIEW);

$prompt = <<<PROMPT
    I want to build a Symfony app in PHP 8.2 that takes user questions and looks them
    up in a database where they are mapped to answers. If there is close match, it
    retrieves the matched answer. If there isn't, it asks the user to provide an answer
    and stores the question/answer pair in the database. Make a plan for the directory 
    structure you'll need, then return each file in full. Only supply your reasoning 
    at the beginning and end, not throughout the code.
    PROMPT;

$response = (new Chain(new PlatformModel($platform, $llm)))->call(new MessageBag(Message::ofUser($prompt)));

echo $response->getContent().PHP_EOL;
