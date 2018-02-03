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

use Jgut\Slim\Routing\Response\ResponseTypeInterface;
use Jgut\Slim\Routing\Response\ViewResponseType;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;
use Slim\Http\Response;
use Slim\Views\Twig;

/**
 * Twig view renderer response handler.
 */
class TwigViewResponseHandler implements ResponseTypeHandlerInterface
{
    /**
     * Twig renderer.
     *
     * @var Twig
     */
    protected $viewRenderer;

    /**
     * TwigViewResponseHandler constructor.
     *
     * @param Twig $viewRenderer
     */
    public function __construct(Twig $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function handle(ResponseTypeInterface $responseType): ResponseInterface
    {
        if (!$responseType instanceof ViewResponseType) {
            throw new \InvalidArgumentException(sprintf('Response type should be %s', ViewResponseType::class));
        }

        return $this->handleResponse($responseType);
    }

    /**
     * Handle response.
     *
     * @param ViewResponseType $responseType
     *
     * @return ResponseInterface
     */
    protected function handleResponse(ViewResponseType $responseType): ResponseInterface
    {
        $responseContent = $this->viewRenderer->fetch($responseType->getTemplate(), $responseType->getParameters());

        $response = $responseType->getResponse();
        if (!$response instanceof ResponseInterface) {
            $response = new Response();
        }

        $body = new Body(fopen('php://temp', 'rb+'));
        $body->write($responseContent);

        return $response->withBody($body);
    }
}
