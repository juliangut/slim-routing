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

use Jgut\Slim\Routing\Response\PayloadResponse;
use Jgut\Slim\Routing\Response\ResponseType;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;
use Slim\Http\Response;

/**
 * Generic JSON response handler.
 */
class JsonResponseHandler implements ResponseTypeHandler
{
    /**
     * Json encode flags.
     *
     * @var int
     */
    protected $jsonFlags;

    /**
     * JsonResponseTypeHandler constructor.
     *
     * @param bool $prettify
     */
    public function __construct(bool $prettify = false)
    {
        $jsonFlags = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION;
        if ($prettify) {
            $jsonFlags |= \JSON_PRETTY_PRINT;
        }

        $this->jsonFlags = $jsonFlags;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function handle(ResponseType $responseType): ResponseInterface
    {
        if (!$responseType instanceof PayloadResponse) {
            throw new \InvalidArgumentException(
                \sprintf('Response type should be an instance of %s', PayloadResponse::class)
            );
        }

        $responseContent = \json_encode($responseType->getPayload(), $this->jsonFlags);

        $response = $responseType->getResponse();
        if (!$response instanceof ResponseInterface) {
            $response = new Response();
        }

        $body = new Body(\fopen('php://temp', 'rb+'));
        $body->write($responseContent);

        return $response->withBody($body);
    }
}
