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

use Jgut\Slim\Routing\Loader\AnnotationLoader;
use Jgut\Slim\Routing\Source\AnnotationSource;
use PHPUnit\Framework\TestCase;

/**
 * Annotation source tests.
 */
class AnnotationSourceTest extends TestCase
{
    public function testLoaderCompiler()
    {
        $source = new AnnotationSource([]);

        self::assertEquals(AnnotationLoader::class, $source->getLoaderClass());
    }
}
