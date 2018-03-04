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

namespace Jgut\Slim\Routing\Tests\Mapping\Metadata;

use Jgut\Slim\Routing\Tests\Stubs\AbstractTransformerStub;
use PHPUnit\Framework\TestCase;

/**
 * Abstract parameter transformer tests.
 */
class AbstractTransformerTest extends TestCase
{
    public function testPrimitiveTransformation()
    {
        $parameters = [
            'one' => 'hi',
            'two' => '1',
            'three' => '5.0',
            'four' => 'false',
        ];

        $definitions = [
            'one' => 'string',
            'two' => 'int',
            'three' => 'float',
            'four' => 'bool',
        ];

        $exception = new \Exception();

        $transformer = new AbstractTransformerStub($exception);

        $parameters = $transformer->transform($parameters, $definitions);

        self::assertEquals('hi', $parameters['one']);
        self::assertEquals(1, $parameters['two']);
        self::assertEquals(5.0, $parameters['three']);
        self::assertEquals(false, $parameters['four']);
    }

    public function testTransformation()
    {
        $parameters = [
            'one' => 'other',
        ];

        $definitions = [
            'one' => \Exception::class,
        ];

        $exception = new \Exception();

        $transformer = new AbstractTransformerStub($exception);

        $parameters = $transformer->transform($parameters, $definitions);

        self::assertSame($exception, $parameters['one']);
    }
}
