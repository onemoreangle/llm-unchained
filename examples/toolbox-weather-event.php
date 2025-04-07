<?php

use PhpLlm\LlmChain\PlatformModel;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Event\ToolCallsExecuted;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\OpenMeteo;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Response\StructuredResponse;
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
            $event->response = new StructuredResponse($toolCallResult->result);
        }
    }
});

$messages = new MessageBag(Message::ofUser('How is the weather currently in Berlin?'));
$response = $chain->call($messages);

dump($response->getContent());
