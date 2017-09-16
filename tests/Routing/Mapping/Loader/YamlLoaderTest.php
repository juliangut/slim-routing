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

namespace Jgut\Slim\Routing\Tests\Mapping\Loader;

use Jgut\Slim\Routing\Mapping\Loader\YamlLoader;
use PHPUnit\Framework\TestCase;

/**
 * YAML loader tests.
 */
class YamlLoaderTest extends TestCase
{
    /**
     * @var YamlLoader
     */
    protected $loader;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->loader = new YamlLoader();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Path "\non\existing\path" does not exist
     */
    public function testInvalidPath()
    {
        $this->loader->getMappingData(['\non\existing\path']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Routing file .+ should return an array$/
     */
    public function testInvalidPathReturn()
    {
        $this->loader->getMappingData([__DIR__ . '/../../Files/files/invalid']);
    }

    public function testValidPath()
    {
        $routing = $this->loader->getMappingData([__DIR__ . '/../../Files/files/valid']);

        $loaded = [
            'parameter1' => 'B',
            'parameter2' => ['A', 'B'],
        ];

        self::assertEquals($loaded, $routing);
    }
}
