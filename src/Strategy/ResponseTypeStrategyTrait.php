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
use RuntimeException;

trait ResponseTypeStrategyTrait
{
    /**
     * @var array<string, string|ResponseTypeHandler>
     */
    protected array $responseHandlers = [];

    protected ResponseFactoryInterface $responseFactory;

    protected ?ContainerInterface $container;

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
     * @param string|ResponseTypeHandler $responseHandler
     */
    public function setResponseHandler(string $type, $responseHandler): void
    {
        $this->responseHandlers[$type] = $responseHandler;
    }

    /**
     * Handle route response.
     *
     * @param ResponseInterface|ResponseType|string|mixed $dispatchedResponse
     *
     * @throws RuntimeException
     */
    protected function handleResponse($dispatchedResponse): ResponseInterface
    {
        if ($dispatchedResponse === null) {
            return $this->responseFactory->createResponse();
        }

        if (\is_string($dispatchedResponse)) {
            $response = $this->responseFactory->createResponse();
            $response->getBody()
                ->write($dispatchedResponse);

            return $response;
        }

        if ($dispatchedResponse instanceof ResponseType) {
            $dispatchedResponse = $this->handleResponseType($dispatchedResponse);
        }

        if (!$dispatchedResponse instanceof ResponseInterface) {
            throw new RuntimeException(sprintf(
                'Handled route response type should be string or "%s". "%s" given.',
                ResponseType::class,
                \gettype($dispatchedResponse),
            ));
        }

        return $dispatchedResponse;
    }

    /**
     * Handle response type.
     *
     * @throws RuntimeException
     */
    protected function handleResponseType(ResponseType $responseType): ResponseInterface
    {
        $type = \get_class($responseType);

        if (!\array_key_exists($type, $this->responseHandlers)) {
            throw new RuntimeException(sprintf('No handler registered for response type "%s".', $type));
        }

        $handler = $this->responseHandlers[$type];
        if (\is_string($handler) && isset($this->container)) {
            $handler = $this->container->get($handler);
        }

        if (!$handler instanceof ResponseTypeHandler) {
            throw new RuntimeException(sprintf(
                'Response handler should implement %s, "%s" given.',
                ResponseTypeHandler::class,
                \is_object($handler) ? \get_class($handler) : \gettype($handler),
            ));
        }

        return $handler->handle($responseType);
    }
}
