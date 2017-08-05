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

namespace Jgut\Slim\Routing\Tests\Annotation;

use Jgut\Slim\Routing\Annotation\Route;
use PHPUnit\Framework\TestCase;

/**
 * Route annotation tests.
 */
class RouteTest extends TestCase
{
    public function testDefaults()
    {
        $annotation = new Route([]);

        self::assertEquals('', $annotation->getName());
        self::assertEquals(['GET'], $annotation->getMethods());
        self::assertEquals(0, $annotation->getPriority());
    }

    public function testName()
    {
        $annotation = new Route(['name' => 'routeName']);

        self::assertEquals('routeName', $annotation->getName());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route annotation methods must be strings. "integer" given
     */
    public function testInvalidMethodsType()
    {
        new Route(['methods' => 10]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route annotation methods can not be empty
     */
    public function testEmptyMethods()
    {
        new Route(['methods' => '']);
    }

    public function testMethods()
    {
        $methods = ['GET', 'POST'];

        $annotation = new Route(['methods' => $methods]);

        self::assertEquals($methods, $annotation->getMethods());
    }

    public function testPriority()
    {
        $annotation = new Route(['priority' => -10]);

        self::assertEquals(-10, $annotation->getPriority());
    }
}
