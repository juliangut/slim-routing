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

namespace Jgut\Slim\Routing\Tests\Strategy;

use Jgut\Slim\Routing\Response\Handler\ResponseTypeHandler;
use Jgut\Slim\Routing\Response\PayloadResponse;
use Jgut\Slim\Routing\Response\ResponseType;
use Jgut\Slim\Routing\Tests\Stubs\ResponseTypeStrategyStub;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ResponseFactory;

/**
 * Trait ResponseTypeStrategyTrait tests.
 */
class ResponseTypeStrategyTraitTest extends TestCase
{
    public function testStringDispatch(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        /* @var ContainerInterface $container */
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        /* @var ServerRequestInterface $request */
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
        /* @var ResponseInterface $response */

        $strategy = new ResponseTypeStrategyStub([], new ResponseFactory(), $container);

        $callback = function (
            ServerRequestInterface $receivedRequest,
            ResponseInterface $receivedResponse
        ) use (
            $request,
            $response
        ) {
            static::assertSame($request, $receivedRequest);
            static::assertSame($response, $receivedResponse);

            return 'Return content';
        };

        $return = $strategy($callback, $request, $response, []);

        static::assertEquals('Return content', (string) $return->getBody());
    }

    public function testResponseDispatch(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        /* @var ContainerInterface $container */
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        /* @var ServerRequestInterface $request */
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
        /* @var ResponseInterface $response */
        $responseFactory = new ResponseFactory();

        $strategy = new ResponseTypeStrategyStub([], $responseFactory, $container);

        $callback = function () use ($responseFactory) {
            $response = $responseFactory->createResponse();
            $response->getBody()->write('Return content');

            return $response;
        };

        $return = $strategy($callback, $request, $response, []);

        static::assertEquals('Return content', (string) $return->getBody());
    }

    public function testNoHandler(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp('/^No handler registered for response type ".+"$/');

        $responseType = $this->getMockBuilder(PayloadResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var ResponseType $responseType */
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        /* @var ContainerInterface $container */
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        /* @var ServerRequestInterface $request */
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
        /* @var ResponseInterface $response */

        $strategy = new ResponseTypeStrategyStub([], new ResponseFactory(), $container);

        $callback = function () use ($responseType) {
            return $responseType;
        };

        $strategy($callback, $request, $response, []);
    }

    public function testWrongHandler(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp(
            '/^Response handler should implement .+\\\ResponseTypeHandler, "stdClass" given$/'
        );

        $responseType = $this->getMockBuilder(PayloadResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var ResponseType $responseType */
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects(static::once())
            ->method('get')
            ->will($this->returnValue(new \stdClass()));
        /* @var ContainerInterface $container */
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        /* @var ServerRequestInterface $request */
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
        /* @var ResponseInterface $response */

        $responseHandlers = [\get_class($responseType) => 'class'];
        $strategy = new ResponseTypeStrategyStub($responseHandlers, new ResponseFactory(), $container);

        $callback = function () use ($responseType) {
            return $responseType;
        };

        $strategy($callback, $request, $response, []);
    }

    public function testResponseTypeDispatch(): void
    {
        $responseFactory = new ResponseFactory();
        $responseType = $this->getMockBuilder(PayloadResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var ResponseType $responseType */
        $responseHandler = $this->getMockBuilder(ResponseTypeHandler::class)
            ->getMock();
        $resultResponse = $responseFactory->createResponse();
        $resultResponse->getBody()->write('Return content');
        $responseHandler->expects(static::once())
            ->method('handle')
            ->will($this->returnValue($resultResponse));
        /* @var ResponseType $responseType */

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects(static::once())
            ->method('get')
            ->will($this->returnValue($responseHandler));
        /* @var ContainerInterface $container */
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        /* @var ServerRequestInterface $request */
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
        /* @var ResponseInterface $response */

        $responseHandlers = [\get_class($responseType) => 'class'];
        $strategy = new ResponseTypeStrategyStub($responseHandlers, $responseFactory, $container);

        $callback = function () use ($responseType) {
            return $responseType;
        };

        $return = $strategy($callback, $request, $response, []);

        static::assertEquals('Return content', (string) $return->getBody());
    }
}
