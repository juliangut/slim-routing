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

use Jgut\Slim\Routing\Naming\CamelCase;
use PHPUnit\Framework\TestCase;

/**
 * Camel case route naming strategy tests.
 */
class CamelCaseTest extends TestCase
{
    /**
     * @var CamelCase
     */
    protected $naming;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->naming = new CamelCase();
    }

    public function testSinglePartName()
    {
        self::assertEquals('name', $this->naming->combine(['name']));
    }

    public function testMultiPartName()
    {
        self::assertEquals('multiPartName', $this->naming->combine(['multi', 'part', 'name']));
    }
}
