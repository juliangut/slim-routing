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

namespace Jgut\Slim\Routing\Tests\Naming;

use Jgut\Slim\Routing\Naming\SnakeCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class SnakeCaseTest extends TestCase
{
    protected SnakeCase $naming;

    protected function setUp(): void
    {
        $this->naming = new SnakeCase();
    }

    public function testSinglePartName(): void
    {
        static::assertEquals('name', $this->naming->combine(['name']));
    }

    public function testMultiPartName(): void
    {
        static::assertEquals('multi_part_name', $this->naming->combine(['multi', 'part', 'name']));
    }
}
