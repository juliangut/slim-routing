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

namespace Jgut\Slim\Routing\Tests\Files\Annotation\Invalid\PrivateDefined;

use Jgut\Slim\Routing\Mapping\Annotation as JSR;

/**
 * Example private method defined route.
 *
 * @JSR\Router()
 */
class PrivateDefinedRoute
{
    /**
     * @JSR\Route(
     *     pattern="/private"
     * )
     */
    private function privateAction()
    {
    }
}
