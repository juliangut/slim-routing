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

use Jgut\Slim\Routing\Response\PayloadResponseType;
use Jgut\Slim\Routing\Response\ResponseTypeInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;
use Slim\Http\Response;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * Generic XML response handler.
 */
class XmlResponseHandler implements ResponseTypeHandlerInterface
{
    /**
     * XML should be prettified.
     *
     * @var bool
     */
    protected $prettify;

    /**
     * JsonResponseTypeHandler constructor.
     *
     * @param bool $prettify
     */
    public function __construct(bool $prettify = false)
    {
        $this->prettify = $prettify;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function handle(ResponseTypeInterface $responseType): ResponseInterface
    {
        if (!$responseType instanceof PayloadResponseType) {
            throw new \InvalidArgumentException(sprintf('Response type should be %s', PayloadResponseType::class));
        }

        return $this->handleResponse($responseType);
    }

    /**
     * Handle response.
     *
     * @param PayloadResponseType $responseType
     *
     * @return ResponseInterface
     */
    protected function handleResponse(PayloadResponseType $responseType): ResponseInterface
    {
        $converter = new ArrayToXml($responseType->getPayload(), '', false);
        $responseContent = $this->prettify ? $this->prettify($converter) : $this->asSingleLine($converter);

        $response = $responseType->getResponse();
        if (!$response instanceof ResponseInterface) {
            $response = new Response();
        }

        $body = new Body(fopen('php://temp', 'rb+'));
        $body->write($responseContent);

        return $response->withBody($body);
    }

    /**
     * Return XML in a single line.
     *
     * @param ArrayToXml $converter
     *
     * @return string
     */
    protected function asSingleLine(ArrayToXml $converter): string
    {
        $xmlLines = explode("\n", $converter->toXml());
        array_walk(
            $xmlLines,
            function (string $xmlLine): string {
                return ltrim($xmlLine);
            }
        );

        return implode('', $xmlLines);
    }

    /**
     * Prettify xml output.
     *
     * @param ArrayToXml $converter
     *
     * @return string
     */
    protected function prettify(ArrayToXml $converter): string
    {
        $domDocument = $converter->toDom();
        $domDocument->formatOutput = true;

        return rtrim($domDocument->saveXML(), "\n");
    }
}
