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

namespace Jgut\Slim\Routing\Tests\Middleware;

use Jgut\Slim\Routing\Middleware\XmlHttpRequestMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * XmlHttpRequest header (AJAX) detection middleware tests.
 */
class XmlHttpRequestMiddlewareTest extends TestCase
{
    public function testPSR15NoAjax()
    {
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->getMock();
        /* @var RequestHandlerInterface $handler */

        $request = Request::createFromEnvironment(new Environment());

        $response = (new XmlHttpRequestMiddleware())->process($request, $handler);

        self::assertEquals(400, $response->getStatusCode());
    }

    public function testPSR15Response()
    {
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->getMock();
        $handler->expects(self::once())
            ->method('handle')
            ->will($this->returnValue(new Response()));
        /* @var RequestHandlerInterface $handler */

        $request = Request::createFromEnvironment(new Environment())
            ->withHeader('X-Requested-With', 'xmlhttprequest');

        $response = (new XmlHttpRequestMiddleware())->process($request, $handler);

        self::assertEquals(200, $response->getStatusCode());
    }

    public function testCallableNoAjax()
    {
        $callable = function ($request, $response) {
            return $response;
        };

        $request = Request::createFromEnvironment(new Environment());

        /** @var Response $response */
        $response = (new XmlHttpRequestMiddleware())($request, new Response(), $callable);

        self::assertEquals(400, $response->getStatusCode());
    }

    public function testCallableResponse()
    {
        $callable = function ($request, $response) {
            return $response;
        };

        $request = Request::createFromEnvironment(new Environment())
            ->withHeader('X-Requested-With', 'xmlhttprequest');

        /** @var Response $response */
        $response = (new XmlHttpRequestMiddleware())($request, new Response(), $callable);

        self::assertEquals(200, $response->getStatusCode());
    }
}
