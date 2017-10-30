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

use Jgut\Slim\Routing\Response\ResponseTypeInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Response type handler interface.
 */
interface ResponseTypeHandlerInterface
{
    /**
     * Handle response.
     *
     * @param ResponseTypeInterface $responseType
     *
     * @return ResponseInterface
     */
    public function handle(ResponseTypeInterface $responseType): ResponseInterface;
}
