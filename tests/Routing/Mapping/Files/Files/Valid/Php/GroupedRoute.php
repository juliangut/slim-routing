<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Files\Php;

use Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Attribute\GroupedRoute;

return [
    [
        'pattern' => '/grouped/{section}',
        'placeholders' => [
            'section' => '[A-Za-z]+',
        ],
        'parameters' => [
            'section' => 'string',
        ],
        'transformers' => ['group-transformer'],
        'middlewares' => ['group-middleware'],
        'routes' => [
            [
                'methods' => ['GET'],
                'pattern' => '/two/{id}',
                'arguments' => [
                    'scope' => 'protected',
                ],
                'parameters' => [
                    'id' => 'int',
                ],
                'transformers' => ['route-transformer'],
                'middlewares' => ['twoMiddleware'],
                'invokable' => GroupedRoute::class . ':actionTwo',
            ],
            [
                'methods' => ['GET'],
                'pattern' => '/three/{id}',
                'priority' => 10,
                'xmlHttpRequest' => true,
                'placeholders' => [
                    'id' => '\d+',
                ],
                'invokable' => GroupedRoute::class . ':actionThree',
            ],
        ],
    ],
];
