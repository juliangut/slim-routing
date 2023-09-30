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

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Files\Php;

use Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Attribute\DependentRoute;

return [
    [
        'pattern' => '/abstract',
        'middlewares' => ['abstractMiddleware'],
        'routes' => [
            [
                'prefix' => 'dependent',
                'pattern' => '/dependent',
                'middlewares' => ['dependentMiddleware'],
                'routes' => [
                    [
                        'name' => 'four',
                        'methods' => ['GET'],
                        'pattern' => '/four',
                        'middlewares' => ['fourMiddleware'],
                        'invokable' => DependentRoute::class . ':actionFour',
                    ],
                ],
            ],
        ],
    ],
];
