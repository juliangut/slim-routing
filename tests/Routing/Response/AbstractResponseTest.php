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

namespace Jgut\Slim\Routing\Tests\Response;

use Jgut\Slim\Routing\Tests\Stubs\ResponseStub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Abstract response tests.
 */
class AbstractResponseTest extends TestCase
{
    public function testResponseType(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        /* @var ServerRequestInterface $request */
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
        /* @var ResponseInterface $response */

        $responseType = new ResponseStub($request, $response);

        static::assertEquals($request, $responseType->getRequest());
        static::assertEquals($response, $responseType->getResponse());
    }
}
