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
    protected function setUp()
    {
        $this->annotation = new PathStub();
    }

    public function testDefaults()
    {
        self::assertEquals('', $this->annotation->getPattern());
        self::assertEquals([], $this->annotation->getPlaceholders());
    }

    public function testPattern()
    {
        $path = '/home/route/path/{id}';

        $this->annotation->setPattern($path);

        self::assertEquals($path, $this->annotation->getPattern());
    }

    public function testPlaceholders()
    {
        $placeholders = [
            'id' => '[0-9]+',
            'name' => '[A-Za-z0-9]',
        ];

        $this->annotation->setPlaceholders($placeholders);

        self::assertEquals($placeholders, $this->annotation->getPlaceholders());
    }
}
