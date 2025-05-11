<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Metadata;

use Jgut\Mapping\Exception\MetadataException;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class RouteMetadataTest extends TestCase
{
    public function testDefaults(): void
    {
        $route = new RouteMetadata('callable');

        static::assertNull($route->getName());
        static::assertNull($route->getGroup());
        static::assertEquals([], $route->getGroupChain());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals('callable', $route->getInvokable());
        static::assertEquals([], $route->getTransformers());
        static::assertEquals(0, $route->getPriority());
        static::assertFalse($route->isXmlHttpRequest());
    }

    public function testInvalidName(): void
    {
        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage('Route name must not contain spaces.');

        (new RouteMetadata('callable'))->setName('invalid name');
    }

    public function testEmptyName(): void
    {
        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage('Route name can not be an empty string.');

        (new RouteMetadata('callable'))->setName('');
    }

    public function testInvalidMethod(): void
    {
        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage('Route method must not contain spaces.');

        (new RouteMetadata('callable'))->setMethods(['invalid method']);
    }

    public function testEmptyMethod(): void
    {
        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage('Route method can not be an empty string.');

        (new RouteMetadata('callable'))->setMethods(['']);
    }

    public function testEmptyMethods(): void
    {
        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage('Route methods can not be empty');

        (new RouteMetadata('callable'))->setMethods([]);
    }

    public function testWrongMethodCount(): void
    {
        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage('Route method "ANY" cannot be defined with other methods');

        (new RouteMetadata('callable'))->setMethods(['GET', 'ANY']);
    }

    public function testTransformer(): void
    {
        $route = new RouteMetadata('callable');

        $route->setTransformers(['transformer']);

        static::assertEquals(['transformer'], $route->getTransformers());
    }
}
