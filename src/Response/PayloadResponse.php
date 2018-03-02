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

/**
 * Generic payload response.
 */
class PayloadResponse extends AbstractResponse
{
    /**
     * Payload.
     *
     * @var array
     */
    protected $payload;

    /**
     * PayloadResponseType constructor.
     *
     * @param array                  $payload
     * @param ServerRequestInterface $request
     * @param ResponseInterface|null $response
     */
    public function __construct(array $payload, ServerRequestInterface $request, ResponseInterface $response = null)
    {
        parent::__construct($request, $response);

        $this->payload = $payload;
    }

    /**
     * Get payload.
     *
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
