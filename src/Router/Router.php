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

use Slim\Router as BaseRouter;

/**
 * Router.
 */
class Router extends BaseRouter implements RouterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        return $this->container;
    }
}
