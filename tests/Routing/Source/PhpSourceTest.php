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

use Jgut\Slim\Routing\Compiler\ArrayCompiler;
use Jgut\Slim\Routing\Loader\PhpLoader;
use Jgut\Slim\Routing\Source\PhpSource;
use PHPUnit\Framework\TestCase;

/**
 * PHP source tests.
 */
class PhpSourceTest extends TestCase
{
    public function testLoaderCompiler()
    {
        $source = new PhpSource([]);

        self::assertEquals(PhpLoader::class, $source->getLoaderClass());
        self::assertEquals(ArrayCompiler::class, $source->getCompilerClass());
    }
}
