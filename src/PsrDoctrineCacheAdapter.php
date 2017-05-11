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

namespace Jgut\Slim\Routing;

use Doctrine\Common\Cache\CacheProvider;
use Psr\SimpleCache\CacheInterface;

/**
 * PSR-16 to Doctrine cache adapter.
 */
class PsrDoctrineCacheAdapter extends CacheProvider
{
    /**
     * SPR-16 cache.
     *
     * @var CacheInterface
     */
    protected $cache;

    /**
     * PsrDoctrineCacheBridge constructor.
     *
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PMD.ShortVariableName)
     */
    protected function doFetch($id)
    {
        return $this->cache->get($id, false);
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PMD.ShortVariableName)
     */
    protected function doContains($id)
    {
        return $this->cache->has($id);
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PMD.ShortVariableName)
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        return $this->cache->set($id, $data, (int) $lifeTime ?: null);
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PMD.ShortVariableName)
     */
    protected function doDelete($id)
    {
        return $this->cache->delete($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        $this->cache->clear();
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        // Do nothing
    }
}
