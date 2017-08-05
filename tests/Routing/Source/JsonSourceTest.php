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

use Jgut\Slim\Routing\Loader\JsonLoader;
use Jgut\Slim\Routing\Source\JsonSource;
use PHPUnit\Framework\TestCase;

/**
 * JSON source tests.
 */
class JsonSourceTest extends TestCase
{
    public function testLoaderCompiler()
    {
        $source = new JsonSource([]);

        self::assertEquals(JsonLoader::class, $source->getLoaderClass());
    }
}
