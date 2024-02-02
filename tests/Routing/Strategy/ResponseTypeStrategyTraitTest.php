<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Strategy;

use Jgut\Slim\Routing\Response\Handler\ResponseTypeHandler;
use Jgut\Slim\Routing\Response\PayloadResponse;
use Jgut\Slim\Routing\Tests\Stubs\ResponseTypeStrategyStub;
use Laminas\Diactoros\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use stdClass;

/**
 * @internal
 */
class ResponseTypeStrategyTraitTest extends TestCase
{
    public function testNullDispatch(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $strategy = new ResponseTypeStrategyStub([], new ResponseFactory(), $container);

        $callback = static function (
            ServerRequestInterface $receivedRequest,
            ResponseInterface $receivedResponse,
        ) use (
            $request,
            $response,
        ): void {
            static::assertSame($request, $receivedRequest);
            static::assertSame($response, $receivedResponse);
        };

        $return = $strategy($callback, $request, $response, []);

        static::assertEquals('', (string) $return->getBody());
    }

    public function testStringDispatch(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $strategy = new ResponseTypeStrategyStub([], new ResponseFactory(), $container);

        $callback = static function (
            ServerRequestInterface $receivedRequest,
            ResponseInterface $receivedResponse,
        ) use (
            $request,
            $response,
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
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
        $responseFactory = new ResponseFactory();

        $strategy = new ResponseTypeStrategyStub([], $responseFactory, $container);

        $callback = static function () use ($responseFactory) {
            $response = $responseFactory->createResponse();
            $response->getBody()
                ->write('Return content');

            return $response;
        };

        $return = $strategy($callback, $request, $response, []);

        static::assertEquals('Return content', (string) $return->getBody());
    }

    public function testInvalidResponseDispatch(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches(
            '/^Handled route response type should be string, null or ".+". "integer" given\.$/',
        );

        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $strategy = new ResponseTypeStrategyStub([], new ResponseFactory(), $container);

        $callback = static fn() => 100;

        $strategy($callback, $request, $response, []);
    }

    public function testNoHandler(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/^No handler registered for response type ".+"\.$/');

        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        $responseType = new PayloadResponse([], $request);
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $strategy = new ResponseTypeStrategyStub([], new ResponseFactory(), $container);

        $callback = static fn() => $responseType;

        $strategy($callback, $request, $response, []);
    }

    public function testWrongHandler(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches(
            '/^Response handler should implement .+\\\ResponseTypeHandler, "stdClass" given\.$/',
        );

        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        $responseType = new PayloadResponse([], $request);
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects(static::once())
            ->method('get')
            ->willReturn(new stdClass());
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $responseHandlers = [$responseType::class => 'class'];
        $strategy = new ResponseTypeStrategyStub($responseHandlers, new ResponseFactory(), $container);

        $callback = static fn() => $responseType;

        $strategy($callback, $request, $response, []);
    }

    public function testResponseTypeDispatch(): void
    {
        $responseFactory = new ResponseFactory();
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        $responseType = new PayloadResponse([], $request);
        $responseHandler = $this->getMockBuilder(ResponseTypeHandler::class)
            ->getMock();
        $resultResponse = $responseFactory->createResponse();
        $resultResponse->getBody()
            ->write('Return content');
        $responseHandler->expects(static::once())
            ->method('handle')
            ->willReturn($resultResponse);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects(static::once())
            ->method('get')
            ->willReturn($responseHandler);
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $responseHandlers = [$responseType::class => 'class'];
        $strategy = new ResponseTypeStrategyStub($responseHandlers, $responseFactory, $container);

        $callback = static fn() => $responseType;

        $return = $strategy($callback, $request, $response, []);

        static::assertEquals('Return content', (string) $return->getBody());
    }
}
