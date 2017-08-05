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
 * Example grouped route.
 *
 * @JSR\Router()
 * @JSR\Group(
 *     name="grouped",
 *     pattern="/grouped/{section}",
 *     placeholders={"section": "[A-Za-z]+"},
 *     middleware={"groupedMiddleware"}
 * )
 */
class GroupedRoute
{
    /**
     * @JSR\Route(
     *     pattern="/two/{id}",
     *     middleware={"twoMiddleware"},
     * )
     */
    public function actionTwo()
    {
    }

    /**
     * @JSR\Route(
     *     pattern="/three/{id}",
     *     placeholders={"id":"\d+"},
     *     middleware={"threeMiddleware"},
     * )
     */
    public function actionThree()
    {
    }
}
