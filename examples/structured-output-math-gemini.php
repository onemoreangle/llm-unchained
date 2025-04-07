<?php

use PhpLlm\LlmChain\PlatformModel;
use PhpLlm\LlmChain\Bridge\Google\GeminiSchemaFactory;
use PhpLlm\LlmChain\Bridge\Google\GoogleModel;
use PhpLlm\LlmChain\Bridge\Google\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\StructuredOutput\ChainProcessor;
use PhpLlm\LlmChain\Chain\StructuredOutput\ResponseFormatFactory;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Tests\Fixture\StructuredOutput\MathReasoning;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['GOOGLE_API_KEY'])) {
    echo 'Please set the GOOGLE_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['GOOGLE_API_KEY']);
$llm = new GoogleModel(GoogleModel::GEMINI_2_FLASH);
$serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

$processor = new ChainProcessor(new ResponseFormatFactory(new GeminiSchemaFactory()), $serializer);
$chain = new Chain(new PlatformModel($platform, $llm), [$processor], [$processor]);
$messages = new MessageBag(
    Message::forSystem('You are a helpful math tutor. Guide the user through the solution step by step.'),
    Message::ofUser('how can I solve 8x + 7 = -23'),
);
$response = $chain->call($messages, ['output_structure' => MathReasoning::class]);

dump($response->getContent());
