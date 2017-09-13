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

use Jgut\Slim\Routing\Tests\Stubs\PathStub;
use PHPUnit\Framework\TestCase;

/**
 * Path annotation trait tests.
 */
class PathTraitTest extends TestCase
{
    public function testDefaults()
    {
        $annotation = new PathStub([]);

        self::assertEquals('/', $annotation->getPattern());
        self::assertEquals([], $annotation->getPlaceholders());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Pattern can not be empty
     */
    public function testEmptyPattern()
    {
        new PathStub(['pattern' => '']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Placeholder matching "[0-9]+" must be defined on placeholders parameter
     */
    public function testInvalidPattern()
    {
        new PathStub(['pattern' => 'path/to/{id:[0-9]+}']);
    }

    public function testPattern()
    {
        $annotation = new PathStub(['pattern' => 'path/to/{id}/']);

        self::assertEquals('/path/to/{id}', $annotation->getPattern());
    }

    public function testPlaceholders()
    {
        $placeholders = [
            'id' => '[0-9]+',
            'name' => '[A-Za-z0-9]',
        ];

        $annotation = new PathStub(['placeholders' => $placeholders]);

        self::assertEquals($placeholders, $annotation->getPlaceholders());
    }
}
