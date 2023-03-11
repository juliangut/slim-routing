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

namespace Jgut\Slim\Routing\Tests\Mapping\Annotation;

use Jgut\Slim\Routing\Tests\Stubs\ArgumentStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ArgumentTraitTest extends TestCase
{
    public function testDefaults(): void
    {
        $annotation = new ArgumentStub();

        static::assertEquals([], $annotation->getArguments());
    }
}
