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

use Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Attribute\GroupedRoute;

return [
    [
        'middleware' => ['groupedMiddleware'],
        'routes' => [
            [
                'methods' => ['GET'],
                'pattern' => '/two/{id}',
                'arguments' => [
                    'scope' => 'protected',
                ],
                'middleware' => ['twoMiddleware'],
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
