<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Invalid\Attribute\UnknownGroup;

use Jgut\Slim\Routing\Mapping\Attribute\Group;
use Jgut\Slim\Routing\Mapping\Attribute\Route;

/**
 * Example unknown group route.
 */
#[Group(parent: 'unknown')]
class UnknownGroupRoute
{
    #[Route(pattern: '/unknown')]
    public function actionUnknown(): void {}
}
