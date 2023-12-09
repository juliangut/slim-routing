<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Naming;

use Jgut\Slim\Routing\Naming\Dot;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class DotTest extends TestCase
{
    protected Dot $naming;

    protected function setUp(): void
    {
        $this->naming = new Dot();
    }

    public function testSinglePartName(): void
    {
        static::assertEquals('name', $this->naming->combine(['name']));
    }

    public function testMultiPartName(): void
    {
        static::assertEquals('multi.part.name', $this->naming->combine(['multi', 'part', 'name']));
    }
}
