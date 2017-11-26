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

namespace Jgut\Slim\Routing\Tests;

use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Response\Handler\ResponseTypeHandlerInterface;
use Jgut\Slim\Routing\Response\ResponseTypeInterface;
use Jgut\Slim\Routing\Route;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Response type aware route tests.
 */
class RouteTest extends TestCase
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->request = Request::createFromEnvironment(Environment::mock());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /No handler registered for response type ".+"/
     */
    public function testNoHandler()
    {
        $responseType = $this->getMockBuilder(ResponseTypeInterface::class)
            ->getMock();
        /* @var ResponseTypeInterface $responseType */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configuration->expects($this->once())
            ->method('getResponseHandlers')
            ->will($this->returnValue([]));
        /* @var Configuration $configuration */

        $route = new Route(
            'GET',
            '/',
            function () use ($responseType) {
                return $responseType;
            }
        );
        $route->setConfiguration($configuration);

        $route($this->request, new Response());
    }

    public function testHandle()
    {
        $responseType = $this->getMockBuilder(ResponseTypeInterface::class)
            ->getMock();
        /* @var ResponseTypeInterface $responseType */

        $response = new Response();

        $responseHandler = $this->getMockBuilder(ResponseTypeHandlerInterface::class)
            ->getMock();
        $responseHandler->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($response));
        /* @var ResponseTypeHandlerInterface $responseHandler */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configuration->expects($this->once())
            ->method('getResponseHandlers')
            ->will($this->returnValue([get_class($responseType) => $responseHandler]));
        /* @var Configuration $configuration */

        $route = new Route(
            'GET',
            '/',
            function () use ($responseType) {
                return $responseType;
            }
        );
        $route->setConfiguration($configuration);

        $handledResponse = $route($this->request, new Response());

        self::assertEquals($response, $handledResponse);
    }

    public function testResponseInterface()
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var Configuration $configuration */

        $response = new Response();

        $route = new Route(
            'GET',
            '/',
            function ($request, $response) {
                return $response;
            }
        );
        $route->setConfiguration($configuration);

        $this->assertEquals($response, $route($this->request, $response));
    }

    public function testStringResponse()
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var Configuration $configuration */

        $route = new Route(
            'GET',
            '/',
            function () {
                return 'response';
            }
        );
        $route->setConfiguration($configuration);

        $this->assertEquals('response', (string) $route($this->request, new Response())->getBody());
    }
}
