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

namespace Jgut\Slim\Routing\Tests\Files\Annotation;

use Jgut\Slim\Routing\Annotation as JSR;

/**
 * Example single route.
 *
 * @JSR\Router()
 */
class SingleRoute
{
    /**
     * @JSR\Route(
     *     methods={"GET", "POST"},
     *     pattern="/one/{id}",
     *     priority=-10,
     *     placeholders={"id": "[0-9]+"},
     *     middleware={"oneMiddleware"},
     *     name="one"
     * )
     */
    public function actionOne()
    {
    }
}
