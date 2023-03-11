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

namespace Jgut\Slim\Routing\Tests\Mapping\Metadata;

use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class RouteMetadataTest extends TestCase
{
    protected RouteMetadata $route;

    protected function setUp(): void
    {
        $this->route = new RouteMetadata('callable', null);
    }

    public function testDefaults(): void
    {
        static::assertEquals('callable', $this->route->getInvokable());
        static::assertNull($this->route->getName());
        static::assertNull($this->route->getGroup());
        static::assertEquals([], $this->route->getGroupChain());
        static::assertEquals([], $this->route->getMethods());
        static::assertNull($this->route->getTransformer());
        static::assertEquals(0, $this->route->getPriority());
        static::assertFalse($this->route->isXmlHttpRequest());
    }

    public function testGroup(): void
    {
        $group = $this->getMockBuilder(GroupMetadata::class)
            ->getMock();

        $this->route->setGroup($group);

        static::assertEquals($group, $this->route->getGroup());
        static::assertEquals([$group], $this->route->getGroupChain());
    }

    public function testTransformer(): void
    {
        $this->route->setTransformer('transformer');

        static::assertEquals('transformer', $this->route->getTransformer());
    }

    public function testMethods(): void
    {
        $methods = ['GET', 'POST', 'DELETE'];

        $this->route->setMethods($methods);

        static::assertEquals($methods, $this->route->getMethods());
    }

    public function testPriority(): void
    {
        $this->route->setPriority(-10);

        static::assertEquals(-10, $this->route->getPriority());
    }

    public function testXmlHttpRequest(): void
    {
        $this->route->setXmlHttpRequest(true);

        static::assertTrue($this->route->isXmlHttpRequest());
    }
}
