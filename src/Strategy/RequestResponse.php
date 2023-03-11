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
class RequestResponse implements RequestHandlerInvocationStrategyInterface
{
    use ResponseTypeStrategyTrait;

    /**
     * @param array<string, string|ResponseTypeHandler> $responseHandlers
     */
    public function __construct(
        array $responseHandlers,
        ResponseFactoryInterface $responseFactory,
        ?ContainerInterface $container = null
    ) {
        $this->setResponseHandlers($responseHandlers);

        $this->responseFactory = $responseFactory;
        $this->container = $container;
    }

    /**
     * Invoke a route callable that implements RequestHandlerInterface.
     *
     * @param array<string, mixed>                                                             $routeArguments
     * @param callable(ServerRequestInterface, ResponseInterface, array<string, mixed>): mixed $callable
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ): ResponseInterface {
        foreach ($routeArguments as $argument => $value) {
            $request = $request->withAttribute($argument, $value);
        }

        return $this->handleResponse($callable($request, $response, $routeArguments));
    }
}
