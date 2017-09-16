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

use Jgut\Slim\Routing\Mapping\Driver\AnnotationDriver;
use Jgut\Slim\Routing\Mapping\Driver\DefinitionFileDriver;
use Jgut\Slim\Routing\Mapping\Driver\DriverFactory;
use PHPUnit\Framework\TestCase;

/**
 * Mapping driver factory tests.
 */
class DriverFactoryTest extends TestCase
{
    public function testAnnotationDriver()
    {
        self::assertInstanceOf(AnnotationDriver::class, DriverFactory::getAnnotationDriver());
    }

    public function testPhpDriver()
    {
        self::assertInstanceOf(DefinitionFileDriver::class, DriverFactory::getPhpDriver());
    }

    public function testJsonDriver()
    {
        self::assertInstanceOf(DefinitionFileDriver::class, DriverFactory::getPhpDriver());
    }

    public function testYamlDriver()
    {
        self::assertInstanceOf(DefinitionFileDriver::class, DriverFactory::getYamlDriver());
    }
}
