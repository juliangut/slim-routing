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

namespace Jgut\Slim\Routing\Tests\Source;

use Doctrine\Common\Annotations\AnnotationReader;
use Jgut\Slim\Routing\Mapping\Driver\AnnotationDriver;
use Jgut\Slim\Routing\Mapping\Loader\AnnotationLoader;
use Jgut\Slim\Routing\Mapping\RouteMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Annotation mapping driver factory tests.
 */
class AnnotationDriverTest extends TestCase
{
    /**
     * @var AnnotationReader
     */
    protected $reader;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->reader = new AnnotationReader();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Routes can not be defined in constructor or destructor in class .+$/
     */
    public function testConstructorDefinedRoute()
    {
        $loader = $this->getMockBuilder(AnnotationLoader::class)
            ->getMock();
        $loader->expects(self::any())
            ->method('getMappingData')
            ->will($this->returnValue(
                ['Jgut\Slim\Routing\Tests\Files\Annotation\Invalid\ConstructorDefined\ConstructorDefinedRoute']
            ));
        /* @var AnnotationLoader $loader */

        $driver = new AnnotationDriver($loader, $this->reader);

        $driver->getRoutingMetadata([]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Routes can not be defined in private or protected methods in class .+$/
     */
    public function testPrivateDefinedRoute()
    {
        $loader = $this->getMockBuilder(AnnotationLoader::class)
            ->getMock();
        $loader->expects(self::any())
            ->method('getMappingData')
            ->will($this->returnValue(
                ['Jgut\Slim\Routing\Tests\Files\Annotation\Invalid\PrivateDefined\PrivateDefinedRoute']
            ));
        /* @var AnnotationLoader $loader */

        $driver = new AnnotationDriver($loader, $this->reader);

        $driver->getRoutingMetadata([]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Class .+ does not define any route$/
     */
    public function testNoRoutesRoute()
    {
        $loader = $this->getMockBuilder(AnnotationLoader::class)
            ->getMock();
        $loader->expects(self::any())
            ->method('getMappingData')
            ->will($this->returnValue(
                ['Jgut\Slim\Routing\Tests\Files\Annotation\Invalid\NoRoutes\NoRoutesRoute']
            ));
        /* @var AnnotationLoader $loader */

        $driver = new AnnotationDriver($loader, $this->reader);

        $driver->getRoutingMetadata([]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Referenced group "unknown" on class .+ is not defined$/
     */
    public function testUnknownGroupRoute()
    {
        $loader = $this->getMockBuilder(AnnotationLoader::class)
            ->getMock();
        $loader->expects(self::any())
            ->method('getMappingData')
            ->will($this->returnValue(
                ['Jgut\Slim\Routing\Tests\Files\Annotation\Invalid\UnknownGroup\UnknownGroupRoute']
            ));
        /* @var AnnotationLoader $loader */

        $driver = new AnnotationDriver($loader, $this->reader);

        $driver->getRoutingMetadata([]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Circular reference detected with group "circular" on class .+$/
     */
    public function testCircularReferenceRoute()
    {
        $loader = $this->getMockBuilder(AnnotationLoader::class)
            ->getMock();
        $loader->expects(self::any())
            ->method('getMappingData')
            ->will($this->returnValue(
                ['Jgut\Slim\Routing\Tests\Files\Annotation\Invalid\CircularReference\CircularReferenceRoute']
            ));
        /* @var AnnotationLoader $loader */

        $driver = new AnnotationDriver($loader, $this->reader);

        $driver->getRoutingMetadata([]);
    }

    public function testRoutes()
    {
        $loader = $this->getMockBuilder(AnnotationLoader::class)
            ->getMock();
        $loader->expects(self::any())
            ->method('getMappingData')
            ->will($this->returnValue([
                'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\AbstractRoute',
                'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\DependentRoute',
                'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\GroupedRoute',
                'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\SingleRoute',
            ]));
        /* @var AnnotationLoader $loader */

        $driver = new AnnotationDriver($loader, $this->reader);

        /* @var RouteMetadata[] $routes */
        $routes = $driver->getRoutingMetadata([]);

        $route = $routes[0];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals(['abstract', 'grouped'], $route->getPrefixes());
        self::assertEquals('four', $route->getName());
        self::assertEquals(0, $route->getPriority());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals('/abstract/dependent/four', $route->getPattern());
        self::assertEquals([], $route->getPlaceholders());
        self::assertEquals(['fourMiddleware', 'dependentMiddleware', 'abstractMiddleware'], $route->getMiddleware());
        self::assertEquals(
            ['Jgut\Slim\Routing\Tests\Files\Annotation\Valid\DependentRoute', 'actionFour'],
            $route->getInvokable()
        );

        $route = $routes[1];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals([], $route->getPrefixes());
        self::assertEquals('', $route->getName());
        self::assertEquals(0, $route->getPriority());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals('/grouped/{section}/two/{id}', $route->getPattern());
        self::assertEquals(['section' => '[A-Za-z]+'], $route->getPlaceholders());
        self::assertEquals(['twoMiddleware', 'groupedMiddleware'], $route->getMiddleware());
        self::assertEquals(
            ['Jgut\Slim\Routing\Tests\Files\Annotation\Valid\GroupedRoute', 'actionTwo'],
            $route->getInvokable()
        );

        $route = $routes[2];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals([], $route->getPrefixes());
        self::assertEquals('', $route->getName());
        self::assertEquals(0, $route->getPriority());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals('/grouped/{section}/three/{id}', $route->getPattern());
        self::assertEquals(['section' => '[A-Za-z]+', 'id' => '\d+'], $route->getPlaceholders());
        self::assertEquals(['groupedMiddleware'], $route->getMiddleware());
        self::assertEquals(
            ['Jgut\Slim\Routing\Tests\Files\Annotation\Valid\GroupedRoute', 'actionThree'],
            $route->getInvokable()
        );

        $route = $routes[3];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals([], $route->getPrefixes());
        self::assertEquals('one', $route->getName());
        self::assertEquals(-10, $route->getPriority());
        self::assertEquals(['GET', 'POST'], $route->getMethods());
        self::assertEquals('/one/{id}', $route->getPattern());
        self::assertEquals(['id' => 'numeric'], $route->getPlaceholders());
        self::assertEquals(['oneMiddleware'], $route->getMiddleware());
        self::assertEquals(
            ['Jgut\Slim\Routing\Tests\Files\Annotation\Valid\SingleRoute', 'actionOne'],
            $route->getInvokable()
        );
    }
}
