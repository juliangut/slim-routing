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

use Jgut\Slim\Routing\Mapping\Driver\AnnotationDriver;
use Jgut\Slim\Routing\Mapping\Source\AnnotationSource;
use PHPUnit\Framework\TestCase;

/**
 * Annotation mapping source tests.
 */
class AnnotationSourceTest extends TestCase
{
    public function testDriver()
    {
        $source = new AnnotationSource([]);

        self::assertInstanceOf(AnnotationDriver::class, $source->getDriver());
    }
}
