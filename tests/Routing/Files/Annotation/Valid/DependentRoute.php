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

namespace Jgut\Slim\Routing\Tests\Files\Annotation\Valid;

use Jgut\Slim\Routing\Annotation as JSR;

/**
 * Example dependent route.
 *
 * @JSR\Router()
 * @JSR\Group(
 *     group="grouped",
 *     pattern="/dependent",
 *     middleware={"dependentMiddleware"}
 * )
 */
class DependentRoute
{
    /**
     * @JSR\Route(
     *     pattern="/four",
     *     middleware={"fourMiddleware"}
     * )
     */
    public function actionFour()
    {
    }
}
