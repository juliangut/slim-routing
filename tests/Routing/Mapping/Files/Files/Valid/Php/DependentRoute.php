<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
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
