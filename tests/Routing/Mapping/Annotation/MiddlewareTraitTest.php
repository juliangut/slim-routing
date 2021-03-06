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
    protected function setUp(): void
    {
        $this->annotation = new MiddlewareStub();
    }

    public function testDefaults(): void
    {
        static::assertEquals([], $this->annotation->getMiddleware());
    }

    public function testInvalidMiddleware(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\AnnotationException::class);
        $this->expectExceptionMessage('Route annotation middleware must be strings. "integer" given');

        $this->annotation->setMiddleware(10);
    }

    public function testMiddleware(): void
    {
        $middleware = [
            'middlewareOne',
            'middlewareTwo',
        ];

        $this->annotation->setMiddleware($middleware);

        static::assertEquals($middleware, $this->annotation->getMiddleware());
    }
}
