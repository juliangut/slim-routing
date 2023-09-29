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

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Attribute;

use Jgut\Slim\Routing\Mapping\Attribute\Group;
use Jgut\Slim\Routing\Mapping\Attribute\Middleware;
use Jgut\Slim\Routing\Mapping\Attribute\Route;
use Jgut\Slim\Routing\Mapping\Attribute\Router;

/**
 * Example dependent route.
 */
#[Router]
#[Group(
    parent: AbstractRoute::class,
    prefix: 'grouped',
    pattern: '/dependent',
)]
#[Middleware('dependentMiddleware')]
class DependentRoute
{
    #[Route(
        name: 'four',
        pattern: '/four',
    )]
    #[Middleware('fourMiddleware')]
    public function actionFour(): void {}
}
