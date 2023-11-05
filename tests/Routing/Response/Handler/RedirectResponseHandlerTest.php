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

use InvalidArgumentException;
use Jgut\Slim\Routing\Response\Handler\RedirectResponseHandler;
use Jgut\Slim\Routing\Response\RedirectResponse;
use Jgut\Slim\Routing\Tests\Stubs\ResponseStub;
use Laminas\Diactoros\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteCollector;
use Slim\Routing\RouteParser;

/**
 * @internal
 */
class RedirectResponseHandlerTest extends TestCase
{
    protected ServerRequestInterface $request;

    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
    }

    public function testInvalidResponseType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Response type should be an instance of Jgut\Slim\Routing\Response\RedirectResponse',
        );

        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();
        $routeCollector = $this->getMockBuilder(RouteCollector::class)
            ->disableOriginalConstructor()
            ->getMock();

        (new RedirectResponseHandler($responseFactory, $routeCollector))->handle(new ResponseStub($this->request));
    }

    public function testNotModified(): void
    {
        $responseFactory = new ResponseFactory();
        $routeCollector = $this->getMockBuilder(RouteCollector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response = (new RedirectResponseHandler($responseFactory, $routeCollector))
            ->handle(RedirectResponse::notModified($this->request));

        static::assertEquals(304, $response->getStatusCode());
        static::assertEquals('', $response->getHeaderLine('Location'));
    }

    public function testUrlRedirect(): void
    {
        $responseFactory = new ResponseFactory();
        $routeCollector = $this->getMockBuilder(RouteCollector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response = (new RedirectResponseHandler($responseFactory, $routeCollector))
            ->handle(RedirectResponse::permanentRedirect('https://example.com', $this->request));

        static::assertEquals('https://example.com', $response->getHeaderLine('Location'));
    }

    public function testPathRedirect(): void
    {
        $responseFactory = new ResponseFactory();
        $routeCollector = $this->getMockBuilder(RouteCollector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response = (new RedirectResponseHandler($responseFactory, $routeCollector))
            ->handle(RedirectResponse::permanentRedirect('/home', $this->request));

        static::assertEquals('/home', $response->getHeaderLine('Location'));
    }

    public function testRouteRedirect(): void
    {
        $responseFactory = new ResponseFactory();
        $routeParser = $this->getMockBuilder(RouteParser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeParser
            ->method('urlFor')
            ->with('home')
            ->willReturn('https://example.com/home');
        $routeCollector = $this->getMockBuilder(RouteCollector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeCollector
            ->method('getRouteParser')
            ->willReturn($routeParser);

        $response = (new RedirectResponseHandler($responseFactory, $routeCollector))
            ->handle(RedirectResponse::permanentRedirect('home', $this->request));

        static::assertEquals('https://example.com/home', $response->getHeaderLine('Location'));
    }
}
