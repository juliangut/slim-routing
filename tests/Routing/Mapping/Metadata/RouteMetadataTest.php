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

    public function testDefaults()
    {
        self::assertNull($this->route->getName());
        self::assertNull($this->route->getGroup());
        self::assertEquals([], $this->route->getGroupChain());
        self::assertEquals([], $this->route->getMethods());
        self::assertNull($this->route->getTransformer());
        self::assertNull($this->route->getInvokable());
        self::assertEquals(0, $this->route->getPriority());
        self::assertFalse($this->route->isXmlHttpRequest());
    }

    public function testName()
    {
        $this->route->setName('route');

        self::assertEquals('route', $this->route->getName());
    }

    public function testGroup()
    {
        $group = $this->getMockBuilder(GroupMetadata::class)
            ->getMock();
        /* @var GroupMetadata $group */

        $this->route->setGroup($group);

        self::assertEquals($group, $this->route->getGroup());
        self::assertEquals([$group], $this->route->getGroupChain());
    }

    public function testTransformer()
    {
        $this->route->setTransformer('transformer');

        self::assertEquals('transformer', $this->route->getTransformer());
    }

    public function testMethods()
    {
        $methods = ['GET', 'POST', 'DELETE'];

        $this->route->setMethods($methods);

        self::assertEquals($methods, $this->route->getMethods());
    }

    public function testInvalidInvokable()
    {
        $this->expectExceptionMessage('Route invokable does not seem to be supported by Slim router');
        $this->expectException(\Jgut\Mapping\Exception\MetadataException::class);
        $this->route->setInvokable(10);
    }

    public function testInvokable()
    {
        $callable = ['containerKey', 'method'];

        $this->route->setInvokable($callable);

        self::assertEquals($callable, $this->route->getInvokable());
    }

    public function testPriority()
    {
        $this->route->setPriority(-10);

        self::assertEquals(-10, $this->route->getPriority());
    }

    public function testXmlHttpRequest()
    {
        $this->route->setXmlHttpRequest(true);

        self::assertTrue($this->route->isXmlHttpRequest());
    }
}
