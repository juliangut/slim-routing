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

use Jgut\Slim\Routing\Tests\Stubs\AnnotationStub;
use PHPUnit\Framework\TestCase;

/**
 * Abstract annotation tests.
 */
class AbstractAnnotationTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The following annotation parameters are not recognized: unknown
     */
    public function testUnknownParameter()
    {
        new AnnotationStub(['unknown' => '']);
    }

    public function testParameters()
    {
        $annotation = new AnnotationStub(['silly' => 'Text']);

        self::assertEquals('Text', $annotation->getSilly());
    }
}
