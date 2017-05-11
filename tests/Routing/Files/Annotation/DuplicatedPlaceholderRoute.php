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
 * Example duplicated placeholder route.
 *
 * @JSR\Router()
 * @JSR\Group(
 *     pattern="/{duplicated}"
 * )
 */
class DuplicatedPlaceholderRoute
{
    /**
     * @JSR\Route(
     *     pattern="/{duplicated}"
     * )
     */
    public function actionDuplicated()
    {
    }
}
