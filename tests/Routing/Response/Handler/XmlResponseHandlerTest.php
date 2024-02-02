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
use Jgut\Slim\Routing\Response\Handler\XmlResponseHandler;
use Jgut\Slim\Routing\Response\PayloadResponse;
use Jgut\Slim\Routing\Tests\Stubs\ResponseStub;
use Laminas\Diactoros\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 */
class XmlResponseHandlerTest extends TestCase
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
            'Response type should be an instance of Jgut\Slim\Routing\Response\PayloadResponse',
        );

        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();

        (new XmlResponseHandler($responseFactory))->handle(new ResponseStub($this->request));
    }

    public function testNonEncodableResponseType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response type payload is not XML encodable');

        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();

        (new XmlResponseHandler($responseFactory))
            ->handle(new PayloadResponse(fopen('php://stdout', 'rb'), $this->request));
    }

    public function testHandleCompressed(): void
    {
        $responseFactory = new ResponseFactory();

        $response = (new XmlResponseHandler($responseFactory))
            ->handle(new PayloadResponse([
                'data' => [
                    'param' => 'value',
                ],
            ], $this->request));

        static::assertEquals('application/xml; charset=utf-8', $response->getHeaderLine('Content-Type'));
        static::assertEquals(
            '<?xml version="1.0"?><root><data><param>value</param></data></root>',
            (string) $response->getBody(),
        );
    }

    public function testHandlePrettified(): void
    {
        $responseFactory = new ResponseFactory();

        $response = (new XmlResponseHandler($responseFactory, true))
            ->handle(new PayloadResponse(new ArrayIterator([
                'data' => [
                    'param' => 'value',
                ],
            ]), $this->request));

        static::assertEquals('application/xml; charset=utf-8', $response->getHeaderLine('Content-Type'));

        $responseContent = <<<'XML'
        <?xml version="1.0"?>
        <root>
          <data>
            <param>value</param>
          </data>
        </root>
        XML;
        static::assertEquals($responseContent, (string) $response->getBody());
    }
}
