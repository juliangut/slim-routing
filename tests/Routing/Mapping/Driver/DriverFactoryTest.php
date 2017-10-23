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

use Jgut\Mapping\Driver\AbstractMappingDriver;
use Jgut\Slim\Routing\Mapping\Driver\AnnotationDriver;
use Jgut\Slim\Routing\Mapping\Driver\DriverFactory;
use Jgut\Slim\Routing\Mapping\Driver\DriverInterface;
use PHPUnit\Framework\TestCase;

/**
 * Custom mapping driver factory tests.
 */
class DriverFactoryTest extends TestCase
{
    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Mapping driver should be of the type Jgut\Mapping\Driver\DriverInterface, string given
     */
    public function testInvalidDriver()
    {
        DriverFactory::getDriver(['driver' => 'invalid']);
    }

    public function testAnnotationDriver()
    {
        self::assertInstanceOf(
            AnnotationDriver::class,
            DriverFactory::getDriver(['type' => DriverInterface::DRIVER_ANNOTATION, 'path' => '/path'])
        );
    }

    public function testPhpDriver()
    {
        self::assertInstanceOf(
            AbstractMappingDriver::class,
            DriverFactory::getDriver(['type' => DriverInterface::DRIVER_PHP, 'path' => '/path'])
        );
    }

    public function testJsonDriver()
    {
        self::assertInstanceOf(
            AbstractMappingDriver::class,
            DriverFactory::getDriver(['type' => DriverInterface::DRIVER_JSON, 'path' => '/path'])
        );
    }

    public function testXmlDriver()
    {
        self::assertInstanceOf(
            AbstractMappingDriver::class,
            DriverFactory::getDriver(['type' => DriverInterface::DRIVER_XML, 'path' => '/path'])
        );
    }

    public function testYamlDriver()
    {
        self::assertInstanceOf(
            AbstractMappingDriver::class,
            DriverFactory::getDriver(['type' => DriverInterface::DRIVER_YAML, 'path' => '/path'])
        );
    }
}
