<?php

use OneMoreAngle\LlmUnchained\PlatformModel;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\GPT;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\PlatformFactory;
use OneMoreAngle\LlmUnchained\Chain;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\ChainProcessor;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Tool\YouTubeTranscriber;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Toolbox;
use OneMoreAngle\LlmUnchained\Model\Message\Message;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$llm = new GPT(GPT::GPT_4O_MINI);

$transcriber = new YouTubeTranscriber(HttpClient::create());
$toolbox = Toolbox::create($transcriber);
$processor = new ChainProcessor($toolbox);
$chain = new Chain(new PlatformModel($platform, $llm), [$processor], [$processor]);

$messages = new MessageBag(Message::ofUser('Please summarize this video for me: https://www.youtube.com/watch?v=6uXW-ulpj0s'));
$response = $chain->call($messages, [
    'stream' => true, // enable streaming of response text
]);

foreach ($response->getContent() as $word) {
    echo $word;
}
echo PHP_EOL;
