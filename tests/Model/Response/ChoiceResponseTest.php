<?php

declare(strict_types=1);

namespace OneMoreAngle\LlmUnchained\Tests\Model\Response;

use OneMoreAngle\LlmUnchained\Exception\InvalidArgumentException;
use OneMoreAngle\LlmUnchained\Model\Response\Choice;
use OneMoreAngle\LlmUnchained\Model\Response\ChoiceModelResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(ChoiceModelResponse::class)]
#[UsesClass(Choice::class)]
#[Small]
final class ChoiceResponseTest extends TestCase
{
    #[Test]
    public function choiceResponseCreation(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $choice1 = new Choice('choice1');
        $choice2 = new Choice(null);
        $choice3 = new Choice('choice3');
        $response = new ChoiceModelResponse($mockResponse, $choice1, $choice2, $choice3);

        self::assertCount(3, $response->getContent());
        self::assertSame('choice1', $response->getContent()[0]->getContent());
        self::assertNull($response->getContent()[1]->getContent());
        self::assertSame('choice3', $response->getContent()[2]->getContent());
    }

    #[Test]
    public function choiceResponseWithNoChoices(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must have at least one choice.');

        $mockResponse = $this->createMock(ResponseInterface::class);
        new ChoiceModelResponse($mockResponse);
    }
}
