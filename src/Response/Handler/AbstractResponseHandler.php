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
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Abstract response handler.
 */
abstract class AbstractResponseHandler implements ResponseTypeHandler
{
    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * AbstractResponseHandler constructor.
     *
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Get response.
     *
     * @param ResponseType $responseType
     *
     * @return ResponseInterface
     */
    protected function getResponse(ResponseType $responseType): ResponseInterface
    {
        return $responseType->getResponse() ?? $this->responseFactory->createResponse();
    }
}
