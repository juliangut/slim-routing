<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Attribute;

use Jgut\Slim\Routing\Mapping\Attribute\Group;
use Jgut\Slim\Routing\Mapping\Attribute\Middleware;
use Jgut\Slim\Routing\Mapping\Attribute\Route;

/**
 * Example dependent route.
 */
#[Group(
    prefix: 'dependent',
    parent: AbstractRoute::class,
    pattern: '/dependent',
)]
#[Middleware('dependentMiddleware')]
class DependentRoute
{
    #[Route(
        name: 'four',
        methods: 'GET',
        pattern: '/four',
    )]
    #[Middleware('fourMiddleware')]
    public function actionFour(): void {}
}
