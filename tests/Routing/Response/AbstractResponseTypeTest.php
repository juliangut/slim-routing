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

use Jgut\Slim\Routing\Tests\Stubs\ResponseTypeStub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Abstract response type tests.
 */
class AbstractResponseTypeTest extends TestCase
{
    /**
     * @var ResponseTypeStub
     */
    protected $responseType;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->responseType = new ResponseTypeStub();
    }

    public function testResponse()
    {
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
        /* @var ResponseInterface $response */

        self::assertNull($this->responseType->getResponse());

        $this->responseType->setResponse($response);

        self::assertEquals($response, $this->responseType->getResponse());
    }
}
