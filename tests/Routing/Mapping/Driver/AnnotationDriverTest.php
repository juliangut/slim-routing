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

        /* @var RouteMetadata[] $routes */
        $routes = $driver->getMetadata();

        $route = $routes[0];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertInstanceOf(GroupMetadata::class, $route->getGroup());
        self::assertEquals('four', $route->getName());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals(
            'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\DependentRoute:actionFour',
            $route->getInvocable()
        );
        self::assertEquals(0, $route->getPriority());
        self::assertEquals('four', $route->getPattern());
        self::assertEquals([], $route->getPlaceholders());
        self::assertEquals(['fourMiddleware'], $route->getMiddleware());

        $route = $routes[1];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertNull($route->getName());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals(
            'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\GroupedRoute:actionTwo',
            $route->getInvocable()
        );
        self::assertEquals(0, $route->getPriority());
        self::assertEquals('two/{id}', $route->getPattern());
        self::assertEquals([], $route->getPlaceholders());
        self::assertEquals(['twoMiddleware'], $route->getMiddleware());

        $route = $routes[2];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertNull($route->getName());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals(
            'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\GroupedRoute:actionThree',
            $route->getInvocable()
        );
        self::assertEquals(0, $route->getPriority());
        self::assertEquals('three/{id}', $route->getPattern());
        self::assertEquals(['id' => '\d+'], $route->getPlaceholders());
        self::assertEquals([], $route->getMiddleware());

        $route = $routes[3];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals('one', $route->getName());
        self::assertEquals(['GET', 'POST'], $route->getMethods());
        self::assertEquals(
            'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\SingleRoute:actionOne',
            $route->getInvocable()
        );
        self::assertEquals(-10, $route->getPriority());
        self::assertEquals('one/{id}', $route->getPattern());
        self::assertEquals(['id' => 'numeric'], $route->getPlaceholders());
        self::assertEquals(['oneMiddleware'], $route->getMiddleware());
        self::assertEquals('fake_transformer', $route->getTransformer());
        self::assertEquals(['id' => 'int'], $route->getParameters());
    }
}
