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

namespace Jgut\Slim\Routing\Tests\Stubs;

use Jgut\Slim\Routing\Strategy\ResponseTypeStrategyTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RequestHandlerInvocationStrategyInterface;

/**
 * Middleware annotation stub.
 */
class ResponseTypeStrategyStub implements RequestHandlerInvocationStrategyInterface
{
    use ResponseTypeStrategyTrait;

    /**
     * ResponseTypeStrategyStub constructor.
     *
     * @param array                    $responseHandlers
     * @param ResponseFactoryInterface $responseFactory
     * @param ContainerInterface       $container
     */
    public function __construct(
        array $responseHandlers,
        ResponseFactoryInterface $responseFactory,
        ContainerInterface $container
    ) {
        $this->responseHandlers = $responseHandlers;
        $this->responseFactory = $responseFactory;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ): ResponseInterface {
        return $this->handleResponse($callable($request, $response, $routeArguments));
    }
}
