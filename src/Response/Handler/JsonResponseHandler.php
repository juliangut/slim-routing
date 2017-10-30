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

use Jgut\Slim\Routing\Response\PayloadResponseType;
use Jgut\Slim\Routing\Response\ResponseTypeInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;
use Slim\Http\Response;

/**
 * Generic json response handler.
 */
class JsonResponseHandler implements ResponseTypeHandlerInterface
{
    /**
     * Json encode flags.
     *
     * @var string
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
    public function handle(ResponseTypeInterface $responseType): ResponseInterface
    {
        if (!$responseType instanceof PayloadResponseType) {
            throw new \InvalidArgumentException(sprintf('Response type should be %s', PayloadResponseType::class));
        }

        return $this->handleResponse($responseType);
    }

    /**
     * Handle response.
     *
     * @param PayloadResponseType $responseType
     *
     * @return ResponseInterface
     */
    protected function handleResponse(PayloadResponseType $responseType): ResponseInterface
    {
        $responseContent = json_encode($responseType->getPayload(), $this->jsonFlags);

        $response = $responseType->getResponse();
        if (!$response instanceof ResponseInterface) {
            $response = new Response();
        }

        $body = new Body(fopen('php://temp', 'rb+'));
        $body->write($responseContent);

        return $response->withBody($body);
    }
}
