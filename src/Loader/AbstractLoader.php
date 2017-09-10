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

namespace Jgut\Slim\Routing\Loader;

use Jgut\Slim\Routing\Configuration;

/**
 * Abstract routing loader.
 */
abstract class AbstractLoader implements LoaderInterface
{
    /**
     * Routing configuration.
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     * RouteCompiler constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }
}
