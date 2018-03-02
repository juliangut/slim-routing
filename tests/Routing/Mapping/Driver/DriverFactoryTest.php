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
    public function setUp()
    {
        $this->factory = new DriverFactory();
    }

    /**
     * @expectedException \Jgut\Mapping\Exception\DriverException
     * @expectedExceptionMessageRegExp /^Metadata mapping driver should be of the type .+, string given/
     */
    public function testInvalidDriver()
    {
        $this->factory->getDriver(['driver' => 'invalid']);
    }

    public function testAnnotationDriver()
    {
        self::assertInstanceOf(
            AnnotationDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_ANNOTATION, 'path' => '/path'])
        );
    }

    public function testPhpDriver()
    {
        self::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_PHP, 'path' => '/path'])
        );
    }

    public function testJsonDriver()
    {
        self::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_JSON, 'path' => '/path'])
        );
    }

    public function testXmlDriver()
    {
        self::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_XML, 'path' => '/path'])
        );
    }

    public function testYamlDriver()
    {
        self::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_YAML, 'path' => '/path'])
        );
    }
}
