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

use Jgut\Slim\Routing\Mapping\Loader\AnnotationLoader;
use PHPUnit\Framework\TestCase;

/**
 * Annotation loader tests.
 */
class AnnotationLoaderTest extends TestCase
{
    /**
     * @var AnnotationLoader
     */
    protected $loader;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->loader = new AnnotationLoader();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Path "\non\existing\path" does not exist
     */
    public function testInvalidPath()
    {
        $this->loader->getMappingData(['\non\existing\path']);
    }

    public function testValidPath()
    {
        $routing = $this->loader->getMappingData([
            __DIR__ . '/../../Files/Annotation/Valid/',
            __DIR__ . '/../../Files/Annotation/Valid/SingleRoute.php',
        ]);

        $loaded = [
            'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\AbstractRoute',
            'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\DependentRoute',
            'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\GroupedRoute',
            'Jgut\Slim\Routing\Tests\Files\Annotation\Valid\SingleRoute',

        ];

        self::assertEquals($loaded, $routing);
    }
}
