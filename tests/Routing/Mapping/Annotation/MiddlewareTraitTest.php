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

namespace Jgut\Slim\Routing\Tests\Mapping\Annotation;

use Jgut\Slim\Routing\Tests\Stubs\MiddlewareStub;
use PHPUnit\Framework\TestCase;

/**
 * Middleware annotation tests.
 */
class MiddlewareTraitTest extends TestCase
{
    /**
     * @var MiddlewareStub
     */
    protected $annotation;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->annotation = new MiddlewareStub();
    }

    public function testDefaults()
    {
        self::assertEquals([], $this->annotation->getMiddleware());
    }

    /**
     * @expectedException \Jgut\Mapping\Exception\AnnotationException
     * @expectedExceptionMessage Route annotation middleware must be strings. "integer" given
     */
    public function testInvalidMiddleware()
    {
        $this->annotation->setMiddleware(10);
    }

    public function testMiddleware()
    {
        $middleware = [
            'middlewareOne',
            'middlewareTwo',
        ];

        $this->annotation->setMiddleware($middleware);

        self::assertEquals($middleware, $this->annotation->getMiddleware());
    }
}
