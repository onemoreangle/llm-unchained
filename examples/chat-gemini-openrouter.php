<?php

use OneMoreAngle\LlmUnchained\PlatformModel;
use OneMoreAngle\LlmUnchained\Bridge\OpenRouter\GenericModel;
use OneMoreAngle\LlmUnchained\Bridge\OpenRouter\PlatformFactory;
use OneMoreAngle\LlmUnchained\Chain;
use OneMoreAngle\LlmUnchained\Model\Message\Message;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENROUTER_KEY'])) {
    echo 'Please set the OPENROUTER_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENROUTER_KEY']);
$llm = new GenericModel('google/gemini-2.0-flash-thinking-exp:free');

$chain = new Chain(new PlatformModel($platform, $llm));
$messages = new MessageBag(
    Message::forSystem('You are a helpful assistant.'),
    Message::ofUser('Tina has one brother and one sister. How many sisters do Tina\'s siblings have?'),
);
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
