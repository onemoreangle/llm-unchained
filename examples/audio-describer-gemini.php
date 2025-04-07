<?php

use PhpLlm\LlmChain\PlatformModel;
use PhpLlm\LlmChain\Bridge\Google\GoogleModel;
use PhpLlm\LlmChain\Bridge\Google\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Model\Message\Content\Audio;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['GOOGLE_API_KEY'])) {
    echo 'Please set the GOOGLE_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['GOOGLE_API_KEY']);
$llm = new GoogleModel(GoogleModel::GEMINI_2_FLASH);

$chain = new Chain(new PlatformModel($platform, $llm));
$messages = new MessageBag(
    Message::ofUser(
        'What is this recording about?',
        Audio::fromFile(dirname(__DIR__).'/tests/Fixture/audio.mp3'),
    ),
);
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
