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

namespace Jgut\Slim\Routing\Mapping\Source;

use Jgut\Slim\Routing\Mapping\Driver\DriverInterface;
use Jgut\Slim\Routing\Mapping\RouteMetadata;

/**
 * Abstract mapping source.
 */
abstract class AbstractSource implements SourceInterface
{
    /**
     * Sources.
     *
     * @var string[]
     */
    protected $paths;

    /**
     * Mapping driver.
     *
     * @var DriverInterface
     */
    protected $driver;

    /**
     * Source constructor.
     *
     * @param string[] $paths
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Get routing metadata.
     *
     * @return RouteMetadata[]
     */
    public function getRoutingMetadata(): array
    {
        return $this->getDriver()->getRoutingMetadata($this->paths);
    }

    /**
     * Get mapping driver.
     *
     * @return DriverInterface
     */
    abstract public function getDriver(): DriverInterface;

    /**
     * Set mapping driver.
     *
     * @param DriverInterface $driver
     */
    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
    }
}
