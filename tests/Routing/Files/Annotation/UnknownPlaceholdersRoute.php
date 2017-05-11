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
 * Example unknown placeholders route.
 *
 * @JSR\Router()
 */
class UnknownPlaceholdersRoute
{
    /**
     * @JSR\Route(
     *     pattern="/only/{one}/placeholder",
     *     placeholders={"one": "a-z", "two": "unknown"}
     * )
     */
    public function actionUnknown()
    {
    }
}
