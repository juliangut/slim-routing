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

use Jgut\Slim\Routing\Response\ResponseType;
use Jgut\Slim\Routing\Response\ViewResponse;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;
use Slim\Http\Response;
use Slim\Views\Twig;

/**
 * Twig view renderer response handler.
 */
class TwigViewResponseHandler implements ResponseTypeHandler
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
    public function handle(ResponseType $responseType): ResponseInterface
    {
        if (!$responseType instanceof ViewResponse) {
            throw new \InvalidArgumentException(
                \sprintf('Response type should be an instance of %s', ViewResponse::class)
            );
        }

        $responseContent = $this->viewRenderer->fetch($responseType->getTemplate(), $responseType->getParameters());

        $response = $responseType->getResponse();
        if (!$response instanceof ResponseInterface) {
            $response = new Response();
        }

        $body = new Body(\fopen('php://temp', 'rb+'));
        $body->write($responseContent);

        return $response->withBody($body);
    }
}
