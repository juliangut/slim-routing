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
        'transformer' => 'fake_transformer',
        'parameters' => [
            'first' => 'value',
            'id' => 'int',
        ],
        'xmlHttpRequest' => true,
        'middleware' => ['oneMiddleware'],
        'invokable' => SingleRoute::class . ':actionOne',
    ],
];
