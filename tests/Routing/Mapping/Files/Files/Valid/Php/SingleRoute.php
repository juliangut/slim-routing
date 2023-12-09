<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Files\Php;

use Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Attribute\SingleRoute;

return [
    [
        'name' => 'one',
        'priority' => -10,
        'methods' => ['GET', 'POST'],
        'pattern' => '/one/{id}',
        'placeholders' => [
            'id' => 'numeric',
        ],
        'transformers' => ['fake_transformer'],
        'parameters' => [
            'first' => 'value',
            'id' => 'int',
        ],
        'xmlHttpRequest' => true,
        'middlewares' => ['oneMiddleware'],
        'invokable' => SingleRoute::class . ':actionOne',
    ],
];
