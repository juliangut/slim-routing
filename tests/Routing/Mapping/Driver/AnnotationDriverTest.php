<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Driver;

use Doctrine\Common\Annotations\AnnotationReader;
use Jgut\Mapping\Exception\DriverException;
use Jgut\Slim\Routing\Mapping\Driver\AnnotationDriver;
use RuntimeException;

/**
 * @internal
 */
class AnnotationDriverTest extends AbstractDriverTestCase
{
    protected AnnotationReader $reader;

    protected function setUp(): void
    {
        $this->reader = new AnnotationReader();
    }

    public function testConstructorDefinedRoute(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessageMatches(
            '/Routes can not be defined in constructor or destructor in class ".+"\.$/',
        );

        $paths = [
            __DIR__ . '/../Files/Classes/Invalid/Annotation/ConstructorDefined/ConstructorDefinedRoute.php',
        ];

        $driver = new AnnotationDriver($paths, $this->reader);

        $driver->getMetadata();
    }

    public function testPrivateDefinedRoute(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches(
            '/Routes can not be defined in private or protected methods in class ".+"\.$/',
        );

        $paths = [
            __DIR__ . '/../Files/Classes/Invalid/Annotation/PrivateDefined/PrivateDefinedRoute.php',
        ];

        $driver = new AnnotationDriver($paths, $this->reader);

        $driver->getMetadata();
    }

    public function testUnknownGroupRoute(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Parent group "unknown" does not exist.');

        $paths = [
            __DIR__ . '/../Files/Classes/Invalid/Annotation/UnknownGroup/UnknownGroupRoute.php',
        ];

        $driver = new AnnotationDriver($paths, $this->reader);

        $driver->getMetadata();
    }

    public function testCircularReferenceRoute(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Circular group reference detected');

        $paths = [
            __DIR__ . '/../Files/Classes/Invalid/Annotation/CircularReference/CircularReferenceRoute.php',
        ];

        $driver = new AnnotationDriver($paths, $this->reader);

        $route = $driver->getMetadata()[0];

        $route->getGroupChain();
    }

    public function testNoRoutesRoute(): void
    {
        $paths = [
            __DIR__ . '/../Files/Classes/Valid/Annotation/NoRoutesRoute.php',
        ];

        $driver = new AnnotationDriver($paths, $this->reader);

        static::assertEmpty($driver->getMetadata());
    }

    public function testRoutes(): void
    {
        $paths = [
            __DIR__ . '/../Files/Classes/Valid/Annotation',
        ];

        $driver = new AnnotationDriver($paths, $this->reader);

        $this->checkResources($driver, 'Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Annotation');
    }
}
