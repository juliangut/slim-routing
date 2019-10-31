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

use Jgut\Slim\Routing\Response\Handler\TwigViewResponseHandler;
use Jgut\Slim\Routing\Response\ViewResponse;
use Jgut\Slim\Routing\Tests\Stubs\ResponseStub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Zend\Diactoros\ResponseFactory;

/**
 * Twig view renderer response handler tests.
 */
class TwigViewResponseHandlerTest extends TestCase
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
    }

    public function testInvalidResponseType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Response type should be an instance of Jgut\Slim\Routing\Response\ViewResponse'
        );

        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();
        /* @var ResponseFactoryInterface $responseFactory */
        $twig = $this->getMockBuilder(Twig::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var Twig $twig */

        (new TwigViewResponseHandler($responseFactory, $twig))->handle(new ResponseStub($this->request));
    }

    public function testHandlePrettified(): void
    {
        $responseFactory = new ResponseFactory();
        $twig = $this->getMockBuilder(Twig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $twig->expects(static::once())
            ->method('fetch')
            ->will($this->returnValue('Template rendered!'));
        /* @var Twig $twig */

        $response = (new TwigViewResponseHandler($responseFactory, $twig))
            ->handle(new ViewResponse('template.twig', [], $this->request));

        static::assertInstanceOf(ResponseInterface::class, $response);
        static::assertEquals('text/html; charset=utf-8', $response->getHeaderLine('Content-Type'));
        static::assertEquals('Template rendered!', (string) $response->getBody());
    }
}
