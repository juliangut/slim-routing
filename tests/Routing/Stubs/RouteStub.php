<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Stubs;

use Jgut\Slim\Routing\Route\Route;

/**
 * @internal
 */
class RouteStub extends Route
{
    protected function resolveCallable($callable)
    {
        return $callable;
    }
}
