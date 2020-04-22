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

use Doctrine\Common\Annotations\AnnotationReader;
use Jgut\Slim\Routing\Mapping\Driver\AnnotationDriver;
use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
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
    protected function setUp(): void
    {
        $this->reader = new AnnotationReader();
    }

    public function testConstructorDefinedRoute(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\DriverException::class);
        $this->expectExceptionMessageRegExp('/Routes can not be defined in constructor or destructor in class .+$/');

        $paths = [
            \dirname(__DIR__, 2) . '/Files/Annotation/Invalid/ConstructorDefined/ConstructorDefinedRoute.php',
        ];

        $driver = new AnnotationDriver($paths, $this->reader);

        $driver->getMetadata();
    }

    public function testPrivateDefinedRoute(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp(
            '/Routes can not be defined in private or protected methods in class .+$/'
        );

        $paths = [
            \dirname(__DIR__, 2) . '/Files/Annotation/Invalid/PrivateDefined/PrivateDefinedRoute.php',
        ];

        $driver = new AnnotationDriver($paths, $this->reader);

        $driver->getMetadata();
    }

    public function testNoRoutesRoute(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp('/Class .+ does not define any route$/');

        $paths = [
            \dirname(__DIR__, 2) . '/Files/Annotation/Invalid/NoRoutes/NoRoutesRoute.php',
        ];

        $driver = new AnnotationDriver($paths, $this->reader);

        $driver->getMetadata();
    }

    public function testUnknownGroupRoute(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Parent group unknown does not exist');

        $paths = [
            \dirname(__DIR__, 2) . '/Files/Annotation/Invalid/UnknownGroup/UnknownGroupRoute.php',
        ];

        $driver = new AnnotationDriver($paths, $this->reader);

        $driver->getMetadata();
    }

    public function testCircularReferenceRoute(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Circular group reference detected');

        $paths = [
            \dirname(__DIR__, 2) . '/Files/Annotation/Invalid/CircularReference/CircularReferenceRoute.php',
        ];

        $driver = new AnnotationDriver($paths, $this->reader);

        $route = $driver->getMetadata()[0];

        $route->getGroupChain();
    }

    public function testRoutes(): void
    {
        $paths = [
            \dirname(__DIR__, 2) . '/Files/Annotation/Valid/AbstractRoute.php',
            \dirname(__DIR__, 2) . '/Files/Annotation/Valid/DependentRoute.php',
            \dirname(__DIR__, 2) . '/Files/Annotation/Valid/GroupedRoute.php',
            \dirname(__DIR__, 2) . '/Files/Annotation/Valid/SingleRoute.php',
        ];

        $driver = new AnnotationDriver($paths, $this->reader);

        $routes = $driver->getMetadata();

        $route = $routes[0];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertInstanceOf(GroupMetadata::class, $route->getGroup());
        static::assertEquals('four', $route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals(
            'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\DependentRoute:actionFour',
            $route->getInvokable()
        );
        static::assertEquals(0, $route->getPriority());
        static::assertEquals('four', $route->getPattern());
        static::assertEquals([], $route->getPlaceholders());
        static::assertEquals([], $route->getArguments());
        static::assertEquals(['fourMiddleware'], $route->getMiddleware());

        $route = $routes[1];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertNull($route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals(
            'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\GroupedRoute:actionTwo',
            $route->getInvokable()
        );
        static::assertEquals(0, $route->getPriority());
        static::assertEquals('two/{id}', $route->getPattern());
        static::assertEquals([], $route->getPlaceholders());
        static::assertEquals(['scope' => 'protected'], $route->getArguments());
        static::assertEquals(['twoMiddleware'], $route->getMiddleware());

        $route = $routes[2];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertNull($route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals(
            'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\GroupedRoute:actionThree',
            $route->getInvokable()
        );
        static::assertEquals(0, $route->getPriority());
        static::assertEquals('three/{id}', $route->getPattern());
        static::assertEquals(['id' => '\d+'], $route->getPlaceholders());
        static::assertEquals([], $route->getArguments());
        static::assertEquals([], $route->getMiddleware());

        $route = $routes[3];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertEquals('one', $route->getName());
        static::assertEquals(['GET', 'POST'], $route->getMethods());
        static::assertEquals(
            'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\SingleRoute:actionOne',
            $route->getInvokable()
        );
        static::assertEquals(-10, $route->getPriority());
        static::assertEquals('one/{id}', $route->getPattern());
        static::assertEquals(['id' => 'numeric'], $route->getPlaceholders());
        static::assertEquals([], $route->getArguments());
        static::assertEquals(['oneMiddleware'], $route->getMiddleware());
        static::assertEquals('fake_transformer', $route->getTransformer());
        static::assertEquals(['id' => 'int'], $route->getParameters());
    }
}
