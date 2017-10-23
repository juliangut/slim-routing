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

use Jgut\Slim\Routing\Mapping\Annotation\Group;
use PHPUnit\Framework\TestCase;

/**
 * Group annotation tests.
 */
class GroupTest extends TestCase
{
    /**
     * @var Group
     */
    protected $annotation;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->annotation = new Group([]);
    }

    public function testDefaults()
    {
        self::assertEquals('', $this->annotation->getName());
        self::assertEquals('', $this->annotation->getParent());
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
        $this->annotation->setName('name');

        self::assertEquals('name', $this->annotation->getName());
    }

    public function testParent()
    {
        $this->annotation->setParent('groupName');

        self::assertEquals('groupName', $this->annotation->getParent());
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
        $this->annotation->setPrefix('prefix');

        self::assertEquals('prefix', $this->annotation->getPrefix());
    }
}
