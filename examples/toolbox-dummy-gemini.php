<?php

use PhpLlm\LlmChain\PlatformModel;
use PhpLlm\LlmChain\Bridge\Google\GoogleModel;
use PhpLlm\LlmChain\Bridge\Google\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\Clock;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use Symfony\Component\Clock\Clock as SymfonyClock;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['GOOGLE_API_KEY'])) {
    echo 'Please set the GOOGLE_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

#[AsTool('weather', description: 'Provides the weather at specific locations', method: 'getWeather')]
class Dummy
{
    public function getWeather(string $location): string
    {
        return match ($location) {
            'Amsterdam' => 'It is always sunny in Amsterdam',
            'Los Angeles' => 'It is freezing cold in Los Angeles',
            default => 'I do not know the weather in '.$location,
        };
    }
}

$platform = PlatformFactory::create($_ENV['GOOGLE_API_KEY']);
$llm = new GoogleModel(GoogleModel::GEMINI_2_FLASH);
$dummy = new Dummy();
$clock = new Clock(new SymfonyClock());

$toolBox = Toolbox::create($dummy, $clock);
$processor = new ChainProcessor($toolBox);
$chain = new Chain(new PlatformModel($platform, $llm), [$processor], [$processor]);

$messages = new MessageBag(
    new SystemMessage('You talk like a pirate'),
    Message::ofUser('What date and time is it? And what is the weather like in Los Angeles, and how about Amsterdam? Is this likely correct?')
);
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
