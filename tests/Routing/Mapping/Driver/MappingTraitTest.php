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

namespace Jgut\Slim\Routing\Tests\Mapping\Driver;

use Jgut\Slim\Routing\Mapping\Driver\MappingTrait;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Definition file mapping driver factory tests.
 */
class MappingTraitTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route methods can not be empty
     */
    public function testEmptyMethods()
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['methods' => ''],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route methods must be a string or string array. "integer" given
     */
    public function testInvalidMethods()
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['methods' => 10],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Placeholder keys must be all strings
     */
    public function testInvalidPlaceholders()
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['placeholders' => ['invalid']],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Middleware must be a string or string array. "integer" given
     */
    public function testInvalidMiddleware()
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['middleware' => 10],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route invokable definition missing
     */
    public function testMissingInvokable()
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                [],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route invokable does not seam to be supported by Slim router
     */
    public function testInvalidInvokable()
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['invokable' => 10],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    public function testRoutes()
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                [
                    'prefix' => 'abstract',
                    'pattern' => '/abstract',
                    'middleware' => ['abstractMiddleware'],
                    'routes' => [
                        [
                            'prefix' => 'grouped',
                            'pattern' => '/dependent',
                            'middleware' => ['dependentMiddleware'],
                            'routes' => [
                                [
                                    'name' => 'four',
                                    'methods' => ['GET'],
                                    'pattern' => '/four',
                                    'middleware' => ['fourMiddleware'],
                                    'invokable' => ['FourRoute', 'actionFour'],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'pattern' => '/grouped/{section}',
                    'placeholders' => [
                        'section' => '[A-Za-z]+',
                    ],
                    'middleware' => ['groupedMiddleware'],
                    'routes' => [
                        [
                            'methods' => ['GET'],
                            'pattern' => '/two/{id}',
                            'middleware' => ['twoMiddleware'],
                            'invokable' => ['TwoRoute', 'actionTwo'],
                        ],
                        [
                            'methods' => ['GET'],
                            'pattern' => '/three/{id}',
                            'placeholders' => [
                                'id' => '\d+',
                            ],
                            'invokable' => ['ThreeRoute', 'actionThree'],
                        ],
                    ],
                ],
                [
                    'name' => 'one',
                    'priority' => -10,
                    'methods' => ['GET', 'POST'],
                    'pattern' => '/one/{id}',
                    'placeholders' => [
                        'id' => 'numeric',
                    ],
                    'middleware' => ['oneMiddleware'],
                    'invokable' => ['OneRoute', 'actionOne'],
                ],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $routes = $driver->getMetadata();

        $route = $routes[0];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals('four', $route->getName());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals(0, $route->getPriority());
        self::assertEquals(['FourRoute', 'actionFour'], $route->getInvokable());
        self::assertEquals('four', $route->getPattern());
        self::assertEquals([], $route->getPlaceholders());
        self::assertEquals(['fourMiddleware'], $route->getMiddleware());

        $route = $routes[1];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertNull($route->getName());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals(0, $route->getPriority());
        self::assertEquals(['TwoRoute', 'actionTwo'], $route->getInvokable());
        self::assertEquals('two/{id}', $route->getPattern());
        self::assertEquals([], $route->getPlaceholders());
        self::assertEquals(['twoMiddleware'], $route->getMiddleware());

        $route = $routes[2];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals('', $route->getName());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals(['ThreeRoute', 'actionThree'], $route->getInvokable());
        self::assertEquals(0, $route->getPriority());
        self::assertEquals('three/{id}', $route->getPattern());
        self::assertEquals(['id' => '\d+'], $route->getPlaceholders());
        self::assertEquals([], $route->getMiddleware());

        $route = $routes[3];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals('one', $route->getName());
        self::assertEquals(['GET', 'POST'], $route->getMethods());
        self::assertEquals(-10, $route->getPriority());
        self::assertEquals(['OneRoute', 'actionOne'], $route->getInvokable());
        self::assertEquals('one/{id}', $route->getPattern());
        self::assertEquals(['id' => 'numeric'], $route->getPlaceholders());
        self::assertEquals(['oneMiddleware'], $route->getMiddleware());
    }
}
