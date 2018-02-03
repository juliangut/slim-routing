<?php

/*
 * slim-routing (https://github.com/juliangut/slim-routing).
 * Slim framework routing.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Response\Handler;

use Jgut\Slim\Routing\Response\Handler\JsonResponseHandler;
use Jgut\Slim\Routing\Response\PayloadResponseType;
use Jgut\Slim\Routing\Tests\Stubs\ResponseTypeStub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Generic JSON response handler tests.
 */
class JsonResponseHandlerTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Response type should be Jgut\Slim\Routing\Response\PayloadResponseType
     */
    public function testInvalidResponseType()
    {
        (new JsonResponseHandler())->handle(new ResponseTypeStub());
    }

    public function testHandleCollapsed()
    {
        $responseType = (new PayloadResponseType())->setPayload(['data' => ['param' => 'value']]);

        $response = (new JsonResponseHandler())->handle($responseType);

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertEquals('{"data":{"param":"value"}}', (string) $response->getBody());
    }

    public function testHandlePrettified()
    {
        $responseType = (new PayloadResponseType())->setPayload(['data' => ['param' => 'value']]);
        $response = (new JsonResponseHandler(true))->handle($responseType);

        self::assertInstanceOf(ResponseInterface::class, $response);

        $responseContent = <<<'JSON'
{
    "data": {
        "param": "value"
    }
}
JSON;
        self::assertEquals($responseContent, (string) $response->getBody());
    }
}
