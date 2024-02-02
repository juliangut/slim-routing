<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Response\Handler;

use ArrayIterator;
use InvalidArgumentException;
use Jgut\Slim\Routing\Response\Handler\TwigViewResponseHandler;
use Jgut\Slim\Routing\Response\ViewResponse;
use Jgut\Slim\Routing\Tests\Stubs\ResponseStub;
use Laminas\Diactoros\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

/**
 * @internal
 */
class TwigViewResponseHandlerTest extends TestCase
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
            'Response type should be an instance of Jgut\Slim\Routing\Response\ViewResponse',
        );

        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();
        $twig = $this->getMockBuilder(Twig::class)
            ->disableOriginalConstructor()
            ->getMock();

        (new TwigViewResponseHandler($responseFactory, $twig))->handle(new ResponseStub($this->request));
    }

    public function testHandle(): void
    {
        $responseFactory = new ResponseFactory();
        $twig = $this->getMockBuilder(Twig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $twig->expects(static::once())
            ->method('fetch')
            ->willReturn('Template rendered!');

        $response = (new TwigViewResponseHandler($responseFactory, $twig))
            ->handle(new ViewResponse('user.twig', new ArrayIterator(['id' => null]), $this->request));

        static::assertEquals('text/html; charset=utf-8', $response->getHeaderLine('Content-Type'));
        static::assertEquals('Template rendered!', (string) $response->getBody());
    }
}
