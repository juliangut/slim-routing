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

use Jgut\Slim\Routing\PsrDoctrineCacheAdapter;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * PSR-16 to Doctrine cache adapter tests.
 */
class PsrDoctrineCacheAdapterTest extends TestCase
{
    public function testAdapter()
    {
        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();
        $cache->expects(self::any())
            ->method('get');
        $cache->expects(self::once())
            ->method('has');
        $cache->expects(self::once())
            ->method('set');
        $cache->expects(self::once())
            ->method('delete');
        $cache->expects(self::once())
            ->method('clear');
        /* @var CacheInterface $cache */

        $adapter = new PsrDoctrineCacheAdapter($cache);

        $adapter->fetch('id');
        $adapter->contains('id');
        $adapter->save('id', 1);
        $adapter->delete('id');
        $adapter->flushAll();
        $adapter->getStats();
    }
}
