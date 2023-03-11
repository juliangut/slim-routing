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

use Jgut\Slim\Routing\Response\PayloadResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 */
class PayloadResponseTest extends TestCase
{
    public function testResponseType(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();

        $responseType = new PayloadResponse(['parameter' => 'value'], $request);

        static::assertEquals(['parameter' => 'value'], $responseType->getPayload());
    }
}
