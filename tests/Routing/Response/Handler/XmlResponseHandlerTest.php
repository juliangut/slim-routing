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

use Jgut\Slim\Routing\Response\Handler\XmlResponseHandler;
use Jgut\Slim\Routing\Response\PayloadResponse;
use Jgut\Slim\Routing\Tests\Stubs\ResponseStub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ResponseFactory;

/**
 * Generic XML response handler tests.
 */
class XmlResponseHandlerTest extends TestCase
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
            'Response type should be an instance of Jgut\Slim\Routing\Response\PayloadResponse'
        );

        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();
        /* @var ResponseFactoryInterface $responseFactory */

        (new XmlResponseHandler($responseFactory))->handle(new ResponseStub($this->request));
    }

    public function testHandleCollapsed(): void
    {
        $responseFactory = new ResponseFactory();

        $response = (new XmlResponseHandler($responseFactory))
            ->handle(new PayloadResponse(['data' => ['param' => 'value']], $this->request));

        static::assertInstanceOf(ResponseInterface::class, $response);
        static::assertEquals(
            '<?xml version="1.0"?><root><data><param>value</param></data></root>',
            (string) $response->getBody()
        );
    }

    public function testHandlePrettified(): void
    {
        $responseFactory = new ResponseFactory();

        $response = (new XmlResponseHandler($responseFactory, true))
            ->handle(new PayloadResponse(['data' => ['param' => 'value']], $this->request));

        static::assertInstanceOf(ResponseInterface::class, $response);

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
