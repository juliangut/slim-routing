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
use Jgut\Slim\Routing\Response\ViewResponseType;
use Jgut\Slim\Routing\Tests\Stubs\ResponseTypeStub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

/**
 * Twig view renderer response handler tests.
 */
class TwigViewResponseHandlerTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Response type should be Jgut\Slim\Routing\Response\ViewResponseType
     */
    public function testInvalidResponseType()
    {
        $twig = $this->getMockBuilder(Twig::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var Twig $twig */

        (new TwigViewResponseHandler($twig))->handle(new ResponseTypeStub());
    }

    public function testHandlePrettified()
    {
        $twig = $this->getMockBuilder(Twig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $twig->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue('Template rendered!'));
        /* @var Twig $twig */

        $responseType = (new ViewResponseType())->setTemplate('template.twig');
        $response = (new TwigViewResponseHandler($twig))->handle($responseType);

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertEquals('Template rendered!', (string) $response->getBody());
    }
}
