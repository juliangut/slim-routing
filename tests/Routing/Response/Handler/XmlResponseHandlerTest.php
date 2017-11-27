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

use Jgut\Slim\Routing\Response\Handler\XmlResponseHandler;
use Jgut\Slim\Routing\Response\PayloadResponseType;
use Jgut\Slim\Routing\Tests\Stubs\ResponseTypeStub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Generic XML response handler tests.
 */
class XmlResponseHandlerTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Response type should be Jgut\Slim\Routing\Response\PayloadResponseType
     */
    public function testInvalidResponseType()
    {
        (new XmlResponseHandler())->handle(new ResponseTypeStub());
    }

    public function testHandleCollapsed()
    {
        $responseType = (new PayloadResponseType())->setPayload(['data' => ['param' => 'value']]);

        $response = (new XmlResponseHandler())->handle($responseType);

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertEquals(
            '<?xml version="1.0"?><root><data><param>value</param></data></root>',
            (string) $response->getBody()
        );
    }

    public function testHandlePrettified()
    {
        $responseType = (new PayloadResponseType())->setPayload(['data' => ['param' => 'value']]);
        $response = (new XmlResponseHandler(true))->handle($responseType);

        self::assertInstanceOf(ResponseInterface::class, $response);

        $responseContent = <<<'XML'
<?xml version="1.0"?>
<root>
  <data>
    <param>value</param>
  </data>
</root>
XML;
        self::assertEquals($responseContent, (string) $response->getBody());
    }
}
