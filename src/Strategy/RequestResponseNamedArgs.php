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
use RuntimeException;
use Slim\Interfaces\RequestHandlerInvocationStrategyInterface;

/**
 * Route callback strategy with route parameters as individual arguments.
 */
class RequestResponseNamedArgs implements RequestHandlerInvocationStrategyInterface
{
    use ResponseTypeStrategyTrait;

    /**
     * @param array<string, string|ResponseTypeHandler> $responseHandlers
     *
     * @throws RuntimeException
     */
    public function __construct(
        array $responseHandlers,
        ResponseFactoryInterface $responseFactory,
        ?ContainerInterface $container = null
    ) {
        if (\PHP_VERSION_ID < 80_000) {
            throw new RuntimeException('Named arguments are only available for PHP >= 8.0.');
        }

        $this->setResponseHandlers($responseHandlers);

        $this->responseFactory = $responseFactory;
        $this->container = $container;
    }

    /**
     * Invoke a route callable that implements RequestHandlerInterface.
     *
     * @param array<string, mixed>                                       $routeArguments
     * @param callable(ServerRequestInterface, ResponseInterface): mixed $callable
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ): ResponseInterface {
        return $this->handleResponse($callable($request, $response, ...$routeArguments));
    }
}
