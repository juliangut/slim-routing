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

namespace Jgut\Slim\Routing\Tests\Loader;

use Jgut\Slim\Routing\Loader\AnnotationLoader;
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
        $this->loader->load(['\non\existing\path']);
    }

    public function testEmptyMethods()
    {
        $classes = $this->loader->load([
            __DIR__ . '/../Files/Annotation',
            __DIR__ . '/../Files/Annotation/SingleRoute.php',
        ]);

        $loaded = [
            \Jgut\Slim\Routing\Tests\Files\Annotation\CircularReferenceRoute::class,
            \Jgut\Slim\Routing\Tests\Files\Annotation\DependentRoute::class,
            \Jgut\Slim\Routing\Tests\Files\Annotation\DuplicatedPlaceholderRoute::class,
            \Jgut\Slim\Routing\Tests\Files\Annotation\GroupedRoute::class,
            \Jgut\Slim\Routing\Tests\Files\Annotation\NoRoutesRoute::class,
            \Jgut\Slim\Routing\Tests\Files\Annotation\SingleRoute::class,
            \Jgut\Slim\Routing\Tests\Files\Annotation\UnknownGroupRoute::class,
            \Jgut\Slim\Routing\Tests\Files\Annotation\UnknownPlaceholdersRoute::class,
        ];

        self::assertEquals($loaded, $classes);
    }
}
