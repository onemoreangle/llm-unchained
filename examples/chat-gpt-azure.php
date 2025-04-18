<?php

use OneMoreAngle\LlmUnchained\PlatformModel;
use OneMoreAngle\LlmUnchained\Bridge\Azure\OpenAI\PlatformFactory;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\GPT;
use OneMoreAngle\LlmUnchained\Chain;
use OneMoreAngle\LlmUnchained\Model\Message\Message;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['AZURE_OPENAI_BASEURL']) || empty($_ENV['AZURE_OPENAI_DEPLOYMENT']) || empty($_ENV['AZURE_OPENAI_VERSION']) || empty($_ENV['AZURE_OPENAI_KEY'])
) {
    echo 'Please set the AZURE_OPENAI_BASEURL, AZURE_OPENAI_DEPLOYMENT, AZURE_OPENAI_VERSION, and AZURE_OPENAI_KEY environment variables.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create(
    $_ENV['AZURE_OPENAI_BASEURL'],
    $_ENV['AZURE_OPENAI_DEPLOYMENT'],
    $_ENV['AZURE_OPENAI_VERSION'],
    $_ENV['AZURE_OPENAI_KEY'],
);
$llm = new GPT(GPT::GPT_4O_MINI);

$chain = new Chain(new PlatformModel($platform, $llm));
$messages = new MessageBag(
    Message::forSystem('You are a pirate and you write funny.'),
    Message::ofUser('What is the Symfony framework?'),
);
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
