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

namespace Jgut\Slim\Routing;

use Jgut\Slim\Routing\Mapping\Resolver;
use Jgut\Slim\Routing\Response\Handler\ResponseTypeHandlerInterface;
use Jgut\Slim\Routing\Response\ResponseTypeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\Strategies\RequestResponse;
use Slim\Route as SlimRoute;

/**
 * Response type aware route.
 */
class Route extends SlimRoute
{
    /**
     * Routing configuration.
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     * Route resolver.
     *
     * @var Resolver
     */
    protected $resolver;

    /**
     * Set routing configuration.
     *
     * @param Configuration $configuration
     */
    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $response = $this->dispatchRoute($request, $response);

        if ($response instanceof ResponseTypeInterface) {
            $response = $this->handleResponseType($response);
        }

        return $response;
    }

    /**
     * Dispatch route.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @throws \Exception
     * @throws \Throwable
     *
     * @return ResponseInterface|ResponseTypeInterface
     *
     * @SuppressWarnings(PMD.CyclomaticComplexity)
     * @SuppressWarnings(PMD.NPathComplexity)
     */
    protected function dispatchRoute(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->callable = $this->resolveCallable($this->callable);

        /** @var \Slim\Interfaces\InvocationStrategyInterface $handler */
        $handler = isset($this->container) ? $this->container->get('foundHandler') : new RequestResponse();

        $dispatchedResponse = $handler($this->callable, $request, $response, $this->arguments);

        if ($dispatchedResponse instanceof ResponseTypeInterface
            || $dispatchedResponse instanceof ResponseInterface
        ) {
            $response = $dispatchedResponse;
        } elseif (is_string($dispatchedResponse)) {
            if ($response->getBody()->isWritable()) {
                $response->getBody()->write($dispatchedResponse);
            }
        }

        return $response;
    }

    /**
     * Handle response type.
     *
     * @param ResponseTypeInterface $responseType
     *
     * @throws \RuntimeException
     *
     * @return ResponseInterface
     */
    protected function handleResponseType(ResponseTypeInterface $responseType): ResponseInterface
    {
        $responseHandlers = $this->configuration->getResponseHandlers();
        $type = get_class($responseType);

        if (!array_key_exists($type, $responseHandlers)) {
            throw new \RuntimeException(sprintf('No handler registered for response type "%s"', $type));
        }

        $handler = $responseHandlers[$type];

        if (is_string($handler) && isset($this->container)) {
            $handler = $this->container->get($handler);
        }

        if (!$handler instanceof ResponseTypeHandlerInterface) {
            throw new \RuntimeException(sprintf(
                'Response handler should implement %s, "%s" given',
                ResponseTypeHandlerInterface::class,
                is_object($handler) ? get_class($handler) : gettype($handler)
            ));
        }

        return $handler->handle($responseType);
    }
}
