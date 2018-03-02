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
     *
     * @param ResponseType $responseType
     *
     * @return ResponseInterface
     */
    public function handle(ResponseType $responseType): ResponseInterface;
}
