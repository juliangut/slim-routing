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

namespace Jgut\Slim\Routing\Tests\Mapping\Annotation;

use Jgut\Slim\Routing\Mapping\Annotation\Route;
use PHPUnit\Framework\TestCase;

/**
 * Route annotation tests.
 */
class RouteTest extends TestCase
{
    /**
     * @var Route
     */
    protected $annotation;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->annotation = new Route([]);
    }

    public function testDefaults()
    {
        self::assertEquals('', $this->annotation->getName());
        self::assertEquals(['GET'], $this->annotation->getMethods());
        self::assertEquals(0, $this->annotation->getPriority());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route name must not contain spaces
     */
    public function testWrongName()
    {
        $this->annotation->setName('a name');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route name can not be empty
     */
    public function testEmptyName()
    {
        $this->annotation->setName('');
    }

    public function testName()
    {
        $this->annotation->setName('routeName');

        self::assertEquals('routeName', $this->annotation->getName());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Route annotation methods must be strings. "integer" given
     */
    public function testInvalidMethodsType()
    {
        $this->annotation->setMethods([10]);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Route annotation methods can not be empty
     */
    public function testEmptyMethods()
    {
        $this->annotation->setMethods('');
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Route "ANY" method cannot be defined with other methods
     */
    public function testWrongMethodCount()
    {
        $this->annotation->setMethods(['GET', 'ANY']);
    }

    public function testMethods()
    {
        $methods = ['GET', 'POST'];

        $this->annotation->setMethods($methods);

        self::assertEquals($methods, $this->annotation->getMethods());
    }

    public function testPriority()
    {
        $this->annotation->setPriority(-10);

        self::assertEquals(-10, $this->annotation->getPriority());
    }
}
