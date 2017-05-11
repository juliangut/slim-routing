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

namespace Jgut\Slim\Routing\Tests;

use Jgut\Slim\Routing\Configuration;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * Configuration tests.
 */
class ConfigurationTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Configurations must be a traversable
     */
    public function testInvalidConfigurations()
    {
        new Configuration('');
    }

    public function testDefaults()
    {
        $configuration = new Configuration();

        self::assertNull($configuration->getCache());
        self::assertEmpty($configuration->getSources());
    }

    public function testPaths()
    {
        $paths = [
            '/path/to/directory',
            '/path/to/file.php',
        ];

        $configuration = new Configuration(['sources' => $paths]);

        self::assertEquals($paths, $configuration->getSources());
    }

    public function testCache()
    {
        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();

        $configuration = new Configuration(['cache' => $cache]);

        self::assertEquals($cache, $configuration->getCache());
    }
}
