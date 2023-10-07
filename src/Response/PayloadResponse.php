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

namespace Jgut\Slim\Routing\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PayloadResponse extends AbstractResponse
{
    public function __construct(
        protected mixed $payload,
        ServerRequestInterface $request,
        ?ResponseInterface $response = null,
    ) {
        parent::__construct($request, $response);
    }

    public function getPayload(): mixed
    {
        return $this->payload;
    }
}
