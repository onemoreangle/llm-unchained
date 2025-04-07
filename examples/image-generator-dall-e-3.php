<?php

use OneMoreAngle\LlmUnchained\Bridge\OpenAI\DallE;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\DallE\ImageModelResponse;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\PlatformFactory;
use OneMoreAngle\LlmUnchained\Model\Response\AsyncModelResponse;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);

$response = $platform->request(
    model: new DallE(version: DallE::DALL_E_3),
    input: 'A cartoon-style elephant with a long trunk and large ears.',
    options: [
        'response_format' => 'url', // Generate response as URL
    ],
);

if ($response instanceof AsyncModelResponse) {
    $response = $response->unwrap();
}

assert($response instanceof ImageModelResponse);

echo 'Revised Prompt: '.$response->revisedPrompt.PHP_EOL.PHP_EOL;

foreach ($response->getContent() as $index => $image) {
    echo 'Image '.$index.': '.$image->url.PHP_EOL;
}
