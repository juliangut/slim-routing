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

class PayloadResponse extends AbstractResponse
{
    /**
     * @var array<mixed>
     */
    protected array $payload;

    /**
     * @param array<mixed> $payload
     */
    public function __construct(array $payload, ServerRequestInterface $request, ?ResponseInterface $response = null)
    {
        parent::__construct($request, $response);

        $this->payload = $payload;
    }

    /**
     * @return array<mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
