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

use Jgut\Slim\Routing\Route\Route;

/**
 * Response type aware route stub.
 */
class RouteStub extends Route
{
    /**
     * {@inheritdoc}
     */
    protected function resolveCallable($callable)
    {
        return $callable;
    }
}
