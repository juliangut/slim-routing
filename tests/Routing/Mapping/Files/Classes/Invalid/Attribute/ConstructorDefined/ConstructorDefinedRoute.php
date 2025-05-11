<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Invalid\Attribute\ConstructorDefined;

use Jgut\Slim\Routing\Mapping\Attribute\Route;

/**
 * Example constructor defined route.
 */
class ConstructorDefinedRoute
{
    #[Route(pattern: '/constructor')]
    public function __construct() {}
}
