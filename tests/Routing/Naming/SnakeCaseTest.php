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
 * Snake case route naming strategy tests.
 */
class SnakeCaseTest extends TestCase
{
    /**
     * @var SnakeCase
     */
    protected $naming;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->naming = new SnakeCase();
    }

    public function testSinglePartName(): void
    {
        self::assertEquals('name', $this->naming->combine(['name']));
    }

    public function testMultiPartName(): void
    {
        self::assertEquals('multi_part_name', $this->naming->combine(['multi', 'part', 'name']));
    }
}
