<?php

use OneMoreAngle\LlmUnchained\PlatformModel;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\GPT;
use OneMoreAngle\LlmUnchained\Bridge\OpenAI\PlatformFactory;
use OneMoreAngle\LlmUnchained\Chain;
use OneMoreAngle\LlmUnchained\Chain\StructuredOutput\ChainProcessor as StructuredOutputProcessor;
use OneMoreAngle\LlmUnchained\Chain\StructuredOutput\ResponseFormatFactory;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\ChainProcessor as ToolProcessor;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Tool\Clock;
use OneMoreAngle\LlmUnchained\Chain\Toolbox\Toolbox;
use OneMoreAngle\LlmUnchained\Model\Message\Message;
use OneMoreAngle\LlmUnchained\Model\Message\MessageBag;
use Symfony\Component\Clock\Clock as SymfonyClock;
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

$clock = new Clock(new SymfonyClock());
$toolbox = Toolbox::create($clock);
$toolProcessor = new ToolProcessor($toolbox);
$serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
$structuredOutputProcessor = new StructuredOutputProcessor(new ResponseFormatFactory(), $serializer);
$chain = new Chain(new PlatformModel($platform, $llm), [$toolProcessor, $structuredOutputProcessor], [$toolProcessor, $structuredOutputProcessor]);

$messages = new MessageBag(Message::ofUser('What date and time is it?'));
$response = $chain->call($messages, ['response_format' => [
    'type' => 'json_schema',
    'json_schema' => [
        'name' => 'clock',
        'strict' => true,
        'schema' => [
            'type' => 'object',
            'properties' => [
                'date' => ['type' => 'string', 'description' => 'The current date in the format YYYY-MM-DD.'],
                'time' => ['type' => 'string', 'description' => 'The current time in the format HH:MM:SS.'],
            ],
            'required' => ['date', 'time'],
            'additionalProperties' => false,
        ],
    ],
]]);

dump($response->getContent());
