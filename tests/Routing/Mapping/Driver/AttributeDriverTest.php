<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Driver;

use Jgut\Mapping\Exception\DriverException;
use Jgut\Slim\Routing\Mapping\Driver\AttributeDriver;
use RuntimeException;

/**
 * @internal
 */
class AttributeDriverTest extends AbstractDriverTestCase
{
    public function testConstructorDefinedRoute(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessageMatches(
            '/Routes can not be defined in constructor or destructor in class ".+"\.$/',
        );

        $paths = [
            __DIR__ . '/../Files/Classes/Invalid/Attribute/ConstructorDefined/ConstructorDefinedRoute.php',
        ];

        $driver = new AttributeDriver($paths);

        $driver->getMetadata();
    }

    public function testPrivateDefinedRoute(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches(
            '/Routes can not be defined in private or protected methods in class ".+"\.$/',
        );

        $paths = [
            __DIR__ . '/../Files/Classes/Invalid/Attribute/PrivateDefined/PrivateDefinedRoute.php',
        ];

        $driver = new AttributeDriver($paths);

        $driver->getMetadata();
    }

    public function testUnknownGroupRoute(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Parent group "unknown" does not exist.');

        $paths = [
            __DIR__ . '/../Files/Classes/Invalid/Attribute/UnknownGroup/UnknownGroupRoute.php',
        ];

        $driver = new AttributeDriver($paths);

        $driver->getMetadata();
    }

    public function testCircularReferenceRoute(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Circular group reference detected');

        $paths = [
            __DIR__ . '/../Files/Classes/Invalid/Attribute/CircularReference/CircularReferenceRoute.php',
        ];

        $driver = new AttributeDriver($paths);

        $route = $driver->getMetadata()[0];

        $route->getGroupChain();
    }

    public function testNoRoutesRoute(): void
    {
        $paths = [
            __DIR__ . '/../Files/Classes/Valid/Attribute/NoRoutesRoute.php',
        ];

        $driver = new AttributeDriver($paths);

        static::assertEmpty($driver->getMetadata());
    }

    public function testRoutes(): void
    {
        $paths = [
            __DIR__ . '/../Files/Classes/Valid/Attribute',
        ];

        $driver = new AttributeDriver($paths);

        $this->checkResources($driver, 'Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Attribute');
    }
}
