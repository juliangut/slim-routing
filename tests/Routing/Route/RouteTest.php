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
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Response\Handler\ResponseTypeHandler;
use Jgut\Slim\Routing\Response\ResponseType;
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
        $responseType = $this->getMockBuilder(ResponseType::class)
            ->getMock();
        /* @var ResponseType $responseType */

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
            },
            $configuration
        );

        $route($this->request, new Response());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Response handler should implement .+, "stdClass" given/
     */
    public function testInvalidHandler()
    {
        $responseType = $this->getMockBuilder(ResponseType::class)
            ->getMock();
        /* @var ResponseType $responseType */

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
            },
            $configuration
        );
        $route->setContainer($container);

        $route($this->request, new Response());
    }

    public function testNonXmlHttpRequestRequest()
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var Configuration $configuration */

        $metadata = $this->getMockBuilder(RouteMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->once())
            ->method('isXmlHttpRequest')
            ->will($this->returnValue(true));
        /* @var RouteMetadata $metadata */

        $route = new Route(
            'GET',
            '/',
            function ($request, $response) {
                return $response;
            },
            $configuration
        );
        $route->setMetadata($metadata);

        $response = $route->run($this->request, new Response());

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testHandleResponseType()
    {
        $responseType = $this->getMockBuilder(ResponseType::class)
            ->getMock();
        /* @var ResponseType $responseType */

        $response = new Response();

        $responseHandler = $this->getMockBuilder(ResponseTypeHandler::class)
            ->getMock();
        $responseHandler->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($response));
        /* @var ResponseTypeHandler $responseHandler */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configuration->expects($this->once())
            ->method('getResponseHandlers')
            ->will($this->returnValue([\get_class($responseType) => $responseHandler]));
        /* @var Configuration $configuration */

        $metadata = $this->getMockBuilder(RouteMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var RouteMetadata $metadata */

        $route = new Route(
            'GET',
            '/',
            function () use ($responseType) {
                return $responseType;
            },
            $configuration
        );
        $route->setMetadata($metadata);

        self::assertEquals($metadata, $route->getMetadata());
        self::assertEquals($response, $route->run($this->request, new Response()));
    }

    public function testHandleResponseInterface()
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
            },
            $configuration
        );

        $this->assertEquals($response, $route->run($this->request, $response));
    }

    public function testHandleStringResponse()
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
            },
            $configuration
        );

        $this->assertEquals('response', (string) $route->run($this->request, new Response())->getBody());
    }
}
