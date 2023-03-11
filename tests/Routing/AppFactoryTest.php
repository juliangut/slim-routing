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

use Jgut\Slim\Routing\AppFactory;
use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\RouteCollector;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class AppFactoryTest extends TestCase
{
    public function testCreation(): void
    {
        AppFactory::setRouteCollectorConfiguration(new Configuration());
        $app = AppFactory::create();

        static::assertInstanceOf(RouteCollector::class, $app->getRouteCollector());
    }
}
