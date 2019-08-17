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

use Jgut\Slim\Routing\Tests\Stubs\PathStub;
use PHPUnit\Framework\TestCase;

/**
 * Path annotation trait tests.
 */
class PathTraitTest extends TestCase
{
    /**
     * @var PathStub
     */
    protected $annotation;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->annotation = new PathStub();
    }

    public function testDefaults(): void
    {
        static::assertEquals('', $this->annotation->getPattern());
        static::assertEquals([], $this->annotation->getPlaceholders());
        static::assertEquals([], $this->annotation->getParameters());
    }

    public function testPattern(): void
    {
        $path = '/home/route/path/{id}';

        $this->annotation->setPattern($path);

        static::assertEquals($path, $this->annotation->getPattern());
    }

    public function testPlaceholders(): void
    {
        $placeholders = [
            'id' => '[0-9]+',
            'name' => '[A-Za-z0-9]',
        ];

        $this->annotation->setPlaceholders($placeholders);

        static::assertEquals($placeholders, $this->annotation->getPlaceholders());
    }

    public function testParameters(): void
    {
        $parameters = [
            'id' => 'int',
            'exception' => \Exception::class,
        ];

        $this->annotation->setParameters($parameters);

        static::assertEquals($parameters, $this->annotation->getParameters());
    }
}
