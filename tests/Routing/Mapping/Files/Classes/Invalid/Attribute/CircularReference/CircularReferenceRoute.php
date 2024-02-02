<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Invalid\Attribute\CircularReference;

use Jgut\Slim\Routing\Mapping\Attribute\Group;
use Jgut\Slim\Routing\Mapping\Attribute\Route;

/**
 * Example circular reference route.
 */
#[Group(parent: CircularReferenceRoute::class)]
class CircularReferenceRoute
{
    #[Route(pattern: '/circular')]
    public function actionCircular(): void {}
}
