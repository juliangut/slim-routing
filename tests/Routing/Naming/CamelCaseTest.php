<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Naming;

use Jgut\Slim\Routing\Naming\CamelCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class CamelCaseTest extends TestCase
{
    protected CamelCase $naming;

    protected function setUp(): void
    {
        $this->naming = new CamelCase();
    }

    public function testSinglePartName(): void
    {
        static::assertEquals('name', $this->naming->combine(['name']));
    }

    public function testMultiPartName(): void
    {
        static::assertEquals('multiPartName', $this->naming->combine(['multi', 'part', 'name']));
    }
}
