<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Response\Handler;

use Jgut\Slim\Routing\Response\ResponseType;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractResponseHandler implements ResponseTypeHandler
{
    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
    ) {}

    protected function getResponse(ResponseType $responseType): ResponseInterface
    {
        return $responseType->getResponse() ?? $this->responseFactory->createResponse();
    }
}
