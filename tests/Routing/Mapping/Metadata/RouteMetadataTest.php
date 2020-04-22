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
 * Route metadata tests.
 */
class RouteMetadataTest extends TestCase
{
    /**
     * @var RouteMetadata
     */
    protected $route;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->route = new RouteMetadata();
    }

    public function testDefaults(): void
    {
        static::assertNull($this->route->getName());
        static::assertNull($this->route->getGroup());
        static::assertEquals([], $this->route->getGroupChain());
        static::assertEquals([], $this->route->getMethods());
        static::assertNull($this->route->getTransformer());
        static::assertNull($this->route->getInvokable());
        static::assertEquals(0, $this->route->getPriority());
        static::assertFalse($this->route->isXmlHttpRequest());
    }

    public function testName(): void
    {
        $this->route->setName('route');

        static::assertEquals('route', $this->route->getName());
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

    public function testInvalidInvokable(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\MetadataException::class);
        $this->expectExceptionMessage('Route invokable does not seem to be supported by Slim router');

        $this->route->setInvokable(10);
    }

    public function testInvokable(): void
    {
        $callable = ['containerKey', 'method'];

        $this->route->setInvokable($callable);

        static::assertEquals($callable, $this->route->getInvokable());
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
