<?php

declare(strict_types=1);

use OneMoreAngle\LlmUnchained\Bridge\OpenAI\Embeddings;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\PlatformFactory;
use OneMoreAngle\LlmUnchained\Model\Response\VectorModelResponse;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$ada = new Embeddings(Embeddings::TEXT_ADA_002);
$small = new Embeddings(Embeddings::TEXT_3_SMALL);
$large = new Embeddings(Embeddings::TEXT_3_LARGE);

echo 'Initiating parallel embeddings calls to platform ...'.PHP_EOL;
$responses = [];
foreach (['ADA' => $ada, 'Small' => $small, 'Large' => $large] as $name => $model) {
    echo ' - Request for model '.$name.' initiated.'.PHP_EOL;
    $responses[] = $platform->request($model, 'Hello, world!');
}

echo 'Waiting for the responses ...'.PHP_EOL;
foreach ($responses as $response) {
    assert($response instanceof VectorModelResponse);
    echo 'Dimensions: '.$response->getContent()[0]->getDimensions().PHP_EOL;
}
