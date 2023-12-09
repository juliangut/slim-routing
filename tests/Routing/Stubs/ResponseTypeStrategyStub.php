<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Stubs;

use Jgut\Slim\Routing\Response\Handler\ResponseTypeHandler;
use Jgut\Slim\Routing\Strategy\ResponseTypeStrategyTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RequestHandlerInvocationStrategyInterface;

/**
 * @internal
 */
class ResponseTypeStrategyStub implements RequestHandlerInvocationStrategyInterface
{
    use ResponseTypeStrategyTrait;

    /**
     * @param array<string, string|ResponseTypeHandler> $responseHandlers
     */
    public function __construct(
        array $responseHandlers,
        protected ResponseFactoryInterface $responseFactory,
        protected ?ContainerInterface $container,
    ) {
        $this->setResponseHandlers($responseHandlers);
    }

    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments,
    ): ResponseInterface {
        return $this->handleResponse($callable($request, $response, $routeArguments));
    }
}
