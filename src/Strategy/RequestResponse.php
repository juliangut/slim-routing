<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Strategy;

use Jgut\Slim\Routing\Response\Handler\ResponseTypeHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RequestHandlerInvocationStrategyInterface;

/**
 * Default route callback strategy with route parameters as an array of arguments.
 */
final class RequestResponse implements RequestHandlerInvocationStrategyInterface
{
    use ResponseTypeStrategyTrait;

    /**
     * @param array<string, string|ResponseTypeHandler> $responseHandlers
     */
    public function __construct(
        array $responseHandlers,
        protected ResponseFactoryInterface $responseFactory,
        protected ?ContainerInterface $container = null,
    ) {
        $this->setResponseHandlers($responseHandlers);
    }

    /**
     * @param callable(ServerRequestInterface, ResponseInterface, array<string, mixed>): mixed $callable
     * @param array<string, mixed>                                                             $routeArguments
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments,
    ): ResponseInterface {
        foreach ($routeArguments as $argument => $value) {
            $request = $request->withAttribute($argument, $value);
        }

        return $this->handleResponse($callable($request, $response, $routeArguments));
    }
}
