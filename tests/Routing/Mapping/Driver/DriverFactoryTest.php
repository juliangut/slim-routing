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

use Jgut\Mapping\Driver\AbstractMappingDriver;
use Jgut\Slim\Routing\Mapping\Driver\AnnotationDriver;
use Jgut\Slim\Routing\Mapping\Driver\DriverFactory;
use PHPUnit\Framework\TestCase;

/**
 * Custom mapping driver factory tests.
 */
class DriverFactoryTest extends TestCase
{
    /**
     * @var DriverFactory
     */
    protected $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->factory = new DriverFactory();
    }

    public function testInvalidDriver(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\DriverException::class);
        $this->expectExceptionMessageMatches(
            '/^Metadata mapping driver should be of the type .+, "string" given/'
        );

        $this->factory->getDriver(['driver' => 'invalid']);
    }

    public function testAnnotationDriver(): void
    {
        static::assertInstanceOf(
            AnnotationDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_ANNOTATION, 'path' => '/path'])
        );
    }

    public function testPhpDriver(): void
    {
        static::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_PHP, 'path' => '/path'])
        );
    }

    public function testJsonDriver(): void
    {
        static::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_JSON, 'path' => '/path'])
        );
    }

    public function testXmlDriver(): void
    {
        static::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_XML, 'path' => '/path'])
        );
    }

    public function testYamlDriver(): void
    {
        static::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_YAML, 'path' => '/path'])
        );
    }
}
