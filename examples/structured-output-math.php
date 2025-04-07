<?php

use OneMoreAngle\LlmUnchained\PlatformModel;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\GPT;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\PlatformFactory;
use OneMoreAngle\LlmUnchained\Chain;
use OneMoreAngle\LlmUnchained\Chain\StructuredOutput\ChainProcessor;
use OneMoreAngle\LlmUnchained\Chain\StructuredOutput\ResponseFormatFactory;
use OneMoreAngle\LlmUnchained\Model\Message\Message;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBag;
use OneMoreAngle\LlmUnchained\Tests\Fixture\StructuredOutput\MathReasoning;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$llm = new GPT(GPT::GPT_4O_MINI);
$serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

$processor = new ChainProcessor(new ResponseFormatFactory(), $serializer);
$chain = new Chain(new PlatformModel($platform, $llm), [$processor], [$processor]);
$messages = new MessageBag(
    Message::forSystem('You are a helpful math tutor. Guide the user through the solution step by step.'),
    Message::ofUser('how can I solve 8x + 7 = -23'),
);
$response = $chain->call($messages, ['output_structure' => MathReasoning::class]);

dump($response->getContent());
