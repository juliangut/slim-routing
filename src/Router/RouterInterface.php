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

namespace Jgut\Slim\Routing\Router;

use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouterInterface as BaseRouterInterface;

interface RouterInterface extends BaseRouterInterface
{
    /**
     * Get container.
     *
     * @return ContainerInterface|null
     */
    public function getContainer();
}
