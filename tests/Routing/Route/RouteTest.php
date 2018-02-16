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

namespace Jgut\Slim\Routing\Tests\Route;

use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Response\Handler\ResponseTypeHandlerInterface;
use Jgut\Slim\Routing\Response\ResponseTypeInterface;
use Jgut\Slim\Routing\Route\Route;
use Jgut\Slim\Routing\Tests\Stubs\RouteStub;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\Strategies\RequestResponse;
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

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Response handler should implement .+, "stdClass" given/
     */
    public function testInvalidHandler()
    {
        $responseType = $this->getMockBuilder(ResponseTypeInterface::class)
            ->getMock();
        /* @var ResponseTypeInterface $responseType */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configuration->expects($this->once())
            ->method('getResponseHandlers')
            ->will($this->returnValue([\get_class($responseType) => 'unknown']));
        /* @var Configuration $configuration */

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap([
                ['foundHandler', new RequestResponse()],
                ['unknown', new \stdClass()],
            ]));
        /* @var ContainerInterface $container */

        $route = new RouteStub(
            'GET',
            '/',
            function () use ($responseType) {
                return $responseType;
            }
        );
        $route->setConfiguration($configuration);
        $route->setContainer($container);

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
            ->will($this->returnValue([\get_class($responseType) => $responseHandler]));
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
