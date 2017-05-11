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
        self::assertEquals('', $annotation->getGroup());
    }

    public function testName()
    {
        $annotation = new Group(['name' => 'name']);

        self::assertEquals('name', $annotation->getName());
    }

    public function testGroup()
    {
        $annotation = new Group(['group' => 'groupName']);

        self::assertEquals('groupName', $annotation->getGroup());
    }
}
