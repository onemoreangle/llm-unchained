<?php

use OneMoreAngle\LlmUnchained\PlatformModel;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\GPT;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\PlatformFactory;
use OneMoreAngle\LlmUnchained\Chain;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\ChainProcessor;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Tool\Tavily;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Toolbox;
use OneMoreAngle\LlmUnchained\Model\Message\Message;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY']) || empty($_ENV['TAVILY_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY and TAVILY_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}
$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$llm = new GPT(GPT::GPT_4O_MINI);

$tavily = new Tavily(HttpClient::create(), $_ENV['TAVILY_API_KEY']);
$toolbox = Toolbox::create($tavily);
$processor = new ChainProcessor($toolbox);
$chain = new Chain(new PlatformModel($platform, $llm), [$processor], [$processor]);

$messages = new MessageBag(Message::ofUser('What was the latest game result of Dallas Cowboys?'));
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
