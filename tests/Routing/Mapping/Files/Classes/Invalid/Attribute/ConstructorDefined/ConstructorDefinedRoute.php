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

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Invalid\Attribute\ConstructorDefined;

use Jgut\Slim\Routing\Mapping\Attribute\Route;
use Jgut\Slim\Routing\Mapping\Attribute\Router;

/**
 * Example constructor defined route.
 */
#[Router]
class ConstructorDefinedRoute
{
    #[Route(pattern: '/constructor')]
    public function __construct()
    {
    }
}