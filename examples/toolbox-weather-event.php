<?php

use OneMoreAngle\LlmUnchained\PlatformModel;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\GPT;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\PlatformFactory;
use OneMoreAngle\LlmUnchained\Chain;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\ChainProcessor;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Event\ToolCallsExecuted;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Tool\OpenMeteo;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Toolbox;
use OneMoreAngle\LlmUnchained\Model\Message\Message;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBag;
use OneMoreAngle\LlmUnchained\Model\Response\StructuredModelResponse;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$llm = new GPT(GPT::GPT_4O_MINI);

$openMeteo = new OpenMeteo(HttpClient::create());
$toolbox = Toolbox::create($openMeteo);
$eventDispatcher = new EventDispatcher();
$processor = new ChainProcessor($toolbox, eventDispatcher: $eventDispatcher);
$chain = new Chain(new PlatformModel($platform, $llm), [$processor], [$processor]);

// Add tool call result listener to enforce chain exits direct with structured response for weather tools
$eventDispatcher->addListener(ToolCallsExecuted::class, function (ToolCallsExecuted $event): void {
    foreach ($event->toolCallResults as $toolCallResult) {
        if (str_starts_with($toolCallResult->toolCall->name, 'weather_')) {
            $event->response = new StructuredModelResponse($event->response->getRawResponse(), $toolCallResult->result);
        }
    }
});

$messages = new MessageBag(Message::ofUser('How is the weather currently in Berlin?'));
$response = $chain->call($messages);

dump($response->getContent());
