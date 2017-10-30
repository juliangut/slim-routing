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

use Jgut\Slim\Routing\Response\PayloadResponseType;
use PHPUnit\Framework\TestCase;

/**
 * Generic json response type tests.
 */
class JsonResponseTypeTest extends TestCase
{
    /**
     * @var PayloadResponseType
     */
    protected $responseType;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->responseType = new PayloadResponseType();
    }

    public function testPayload()
    {
        self::assertNull($this->responseType->getPayload());

        $this->responseType->setPayload(['parameter' => 'value']);

        self::assertEquals(['parameter' => 'value'], $this->responseType->getPayload());
    }
}
