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

use Jgut\Slim\Routing\Annotation\Group;
use PHPUnit\Framework\TestCase;

/**
 * Group annotation tests.
 */
class GroupTest extends TestCase
{
    public function testDefaults()
    {
        $annotation = new Group([]);

        self::assertEquals('', $annotation->getName());
        self::assertEquals('', $annotation->getParent());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Group name must not contain spaces
     */
    public function testWrongName()
    {
        new Group(['name' => 'a name']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Group name can not be empty
     */
    public function testEmptyName()
    {
        new Group(['name' => '']);
    }

    public function testName()
    {
        $annotation = new Group(['name' => 'name']);

        self::assertEquals('name', $annotation->getName());
    }

    public function testParent()
    {
        $annotation = new Group(['parent' => 'groupName']);

        self::assertEquals('groupName', $annotation->getParent());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Group prefixes must not contain spaces
     */
    public function testWrongPrefix()
    {
        new Group(['prefix' => 'a prefix']);
    }

    public function testPrefix()
    {
        $annotation = new Group(['prefix' => 'prefix']);

        self::assertEquals('prefix', $annotation->getPrefix());
    }
}
