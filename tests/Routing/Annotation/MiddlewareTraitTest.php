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

namespace Jgut\Slim\Routing\Tests\Annotation;

use Jgut\Slim\Routing\Tests\Stubs\MiddlewareStub;
use PHPUnit\Framework\TestCase;

/**
 * Middleware annotation tests.
 */
class MiddlewareTraitTest extends TestCase
{
    public function testDefaults()
    {
        $annotation = new MiddlewareStub([]);

        self::assertEquals([], $annotation->getMiddleware());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Middleware annotation must be a string or string array. "integer" given
     */
    public function testInvalidMiddleware()
    {
        new MiddlewareStub(['middleware' => 10]);
    }

    public function testMiddleware()
    {
        $middleware = [
            'middlewareOne',
            'middlewareTwo',
        ];

        $annotation = new MiddlewareStub(['middleware' => $middleware]);

        self::assertEquals($middleware, $annotation->getMiddleware());
    }
}
