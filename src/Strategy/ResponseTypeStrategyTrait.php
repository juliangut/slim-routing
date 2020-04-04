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
use Jgut\Slim\Routing\Response\ResponseType;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait ResponseTypeStrategyTrait.
 */
trait ResponseTypeStrategyTrait
{
    /**
     * @var array<string, string|ResponseTypeHandler>
     */
    protected $responseHandlers = [];

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @var ContainerInterface|null
     */
    protected $container;

    /**
     * Set response type handlers.
     *
     * @param array<string, string|ResponseTypeHandler> $responseHandlers
     */
    public function setResponseHandlers(array $responseHandlers): void
    {
        $this->responseHandlers = [];

        foreach ($responseHandlers as $type => $responseHandler) {
            $this->setResponseHandler($type, $responseHandler);
        }
    }

    /**
     * Set response type handler.
     *
     * @param string                     $type
     * @param string|ResponseTypeHandler $responseHandler
     */
    public function setResponseHandler(string $type, $responseHandler): void
    {
        $this->responseHandlers[$type] = $responseHandler;
    }

    /**
     * Handle route response.
     *
     * @param ResponseInterface|ResponseType|string|null $dispatchedResponse
     *
     * @return ResponseInterface
     */
    protected function handleResponse($dispatchedResponse): ResponseInterface
    {
        if ($dispatchedResponse === null) {
            return $this->responseFactory->createResponse();
        }

        if (\is_string($dispatchedResponse)) {
            $response = $this->responseFactory->createResponse();
            $response->getBody()->write($dispatchedResponse);

            return $response;
        }

        if ($dispatchedResponse instanceof ResponseType) {
            $dispatchedResponse = $this->handleResponseType($dispatchedResponse);
        }

        return $dispatchedResponse;
    }

    /**
     * Handle response type.
     *
     * @param ResponseType $responseType
     *
     * @throws \RuntimeException
     *
     * @return ResponseInterface
     */
    protected function handleResponseType(ResponseType $responseType): ResponseInterface
    {
        $type = \get_class($responseType);

        if (!isset($this->responseHandlers[$type])) {
            throw new \RuntimeException(\sprintf('No handler registered for response type "%s"', $type));
        }

        $handler = $this->responseHandlers[$type];
        if (\is_string($handler) && isset($this->container)) {
            $handler = $this->container->get($handler);
        }

        if (!$handler instanceof ResponseTypeHandler) {
            throw new \RuntimeException(\sprintf(
                'Response handler should implement %s, "%s" given',
                ResponseTypeHandler::class,
                \is_object($handler) ? \get_class($handler) : \gettype($handler)
            ));
        }

        return $handler->handle($responseType);
    }
}
