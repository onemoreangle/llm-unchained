<?php

use OneMoreAngle\LlmUnchained\Bridge\OpenAI\PlatformFactory;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Whisper;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Whisper\File;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$model = new Whisper();
$file = new File(dirname(__DIR__).'/tests/Fixture/audio.mp3');

$response = $platform->request($model, $file);

echo $response->getContent().PHP_EOL;
