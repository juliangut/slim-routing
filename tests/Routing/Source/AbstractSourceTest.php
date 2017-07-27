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

use Jgut\Slim\Routing\Tests\Stubs\SourceStub;
use PHPUnit\Framework\TestCase;

/**
 * Abstract source tests.
 */
class AbstractSourceTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Paths must be a string or iterable, "integer" given
     */
    public function testInvalidPath()
    {
        new SourceStub(10);
    }

    public function testPaths()
    {
        $paths = [
            '/path/to/dir',
        ];

        $source = new SourceStub($paths);

        self::assertEquals($paths, $source->getPaths());
    }
}
