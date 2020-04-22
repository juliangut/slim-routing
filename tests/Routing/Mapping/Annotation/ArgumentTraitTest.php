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
 * Argument annotation tests.
 */
class ArgumentTraitTest extends TestCase
{
    /**
     * @var ArgumentStub
     */
    protected $annotation;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->annotation = new ArgumentStub();
    }

    public function testDefaults(): void
    {
        static::assertEquals([], $this->annotation->getArguments());
    }

    public function testArguments(): void
    {
        $middleware = [
            'argumentOne' => 'value',
            'argumentTwo' => 'value',
        ];

        $this->annotation->setArguments($middleware);

        static::assertEquals($middleware, $this->annotation->getArguments());
    }
}
