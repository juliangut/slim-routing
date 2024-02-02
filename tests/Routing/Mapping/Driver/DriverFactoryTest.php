<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Driver;

use Jgut\Mapping\Driver\AbstractMappingDriver;
use Jgut\Mapping\Driver\DriverFactoryInterface;
use Jgut\Mapping\Exception\DriverException;
use Jgut\Slim\Routing\Mapping\Driver\AnnotationDriver;
use Jgut\Slim\Routing\Mapping\Driver\DriverFactory;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
class DriverFactoryTest extends TestCase
{
    protected DriverFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new DriverFactory();
    }

    public function testInvalidDriver(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessageMatches(
            '/^Metadata mapping driver should be of the type .+, "stdClass" given\.$/',
        );

        $this->factory->getDriver(['driver' => new stdClass()]);
    }

    public function testAnnotationDriver(): void
    {
        static::assertInstanceOf(
            AnnotationDriver::class,
            $this->factory->getDriver(['type' => DriverFactoryInterface::DRIVER_ANNOTATION, 'path' => '/path']),
        );
    }

    public function testPhpDriver(): void
    {
        static::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactoryInterface::DRIVER_PHP, 'path' => '/path']),
        );
    }

    public function testJsonDriver(): void
    {
        static::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactoryInterface::DRIVER_JSON, 'path' => '/path']),
        );
    }

    public function testXmlDriver(): void
    {
        static::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactoryInterface::DRIVER_XML, 'path' => '/path']),
        );
    }

    public function testYamlDriver(): void
    {
        static::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactoryInterface::DRIVER_YAML, 'path' => '/path']),
        );
    }
}
