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

namespace Jgut\Slim\Routing\Tests\Stubs;

use Jgut\Slim\Routing\Mapping\Driver\DriverInterface;
use Jgut\Slim\Routing\Mapping\Source\AbstractSource;

/**
 * Abstract source stub.
 */
class SourceStub extends AbstractSource
{
    /**
     * SourceStub constructor.
     *
     * @param array                $paths
     * @param DriverInterface|null $driver
     */
    public function __construct(array $paths, DriverInterface $driver = null)
    {
        parent::__construct($paths);

        $this->driver = $driver;
    }

    /**
     * {@inheritdoc}
     */
    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }
}
