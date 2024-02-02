<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Invalid\Attribute\PrivateDefined;

use Jgut\Slim\Routing\Mapping\Attribute\Route;

/**
 * Example private method defined route.
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
 */
class PrivateDefinedRoute
{
    #[Route(pattern: '/private')]
    private function privateAction(): void {}
}
