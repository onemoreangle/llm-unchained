<?php

use OneMoreAngle\LlmUnchained\PlatformModel;
use OneMoreAngle\LlmUnchained\Bridge\Google\GoogleModel;
use OneMoreAngle\LlmUnchained\Bridge\Google\PlatformFactory;
use OneMoreAngle\LlmUnchained\Chain;
use OneMoreAngle\LlmUnchained\Model\Message\Content\Audio;
use OneMoreAngle\LlmUnchained\Model\Message\Message;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBag;
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
$data = json_decode($response->getRawResponse()->getContent(false), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo 'Failed to decode JSON response: '.json_last_error_msg().PHP_EOL;
    exit(1);
}
echo "Total tokens used: {$data['usageMetadata']['promptTokenCount']} input / {$data['usageMetadata']['totalTokenCount']} total".PHP_EOL;
