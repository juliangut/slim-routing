<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Response\Handler;

use Jgut\Slim\Routing\Response\ResponseType;
use Psr\Http\Message\ResponseInterface;

/**
 * Response type handler.
 */
interface ResponseTypeHandler
{
    /**
     * Handle response.
     */
    public function handle(ResponseType $responseType): ResponseInterface;
}
