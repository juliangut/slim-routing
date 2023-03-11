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

use Exception;
use Jgut\Slim\Routing\Tests\Stubs\AbstractTransformerStub;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @internal
 */
class AbstractTransformerTest extends TestCase
{
    public function testPrimitiveTransformation(): void
    {
        $parameters = [
            'one' => 'hi',
            'two' => '1',
            'three' => '5.0',
            'four' => 'on',
        ];

        $definitions = [
            'one' => 'string',
            'two' => 'int',
            'three' => 'float',
            'four' => 'bool',
        ];

        $exception = new Exception();

        $transformer = new AbstractTransformerStub($exception);

        $parameters = $transformer->transform($parameters, $definitions);

        static::assertEquals('hi', $parameters['one']);
        static::assertEquals(1, $parameters['two']);
        static::assertEquals(5.0, $parameters['three']);
        static::assertTrue($parameters['four']);
    }

    public function testTransformation(): void
    {
        $parameters = [
            'one' => 'other',
        ];

        $definitions = [
            'one' => Throwable::class,
        ];

        $exception = new Exception();

        $transformer = new AbstractTransformerStub($exception);

        $parameters = $transformer->transform($parameters, $definitions);

        static::assertSame($exception, $parameters['one']);
    }
}
