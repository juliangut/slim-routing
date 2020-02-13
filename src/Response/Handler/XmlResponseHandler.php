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

use Jgut\Slim\Routing\Response\PayloadResponse;
use Jgut\Slim\Routing\Response\ResponseType;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * Generic XML response handler.
 */
class XmlResponseHandler extends AbstractResponseHandler
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
     * @param ResponseFactoryInterface $responseFactory
     * @param bool                     $prettify
     */
    public function __construct(ResponseFactoryInterface $responseFactory, bool $prettify = false)
    {
        parent::__construct($responseFactory);

        $this->prettify = $prettify;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function handle(ResponseType $responseType): ResponseInterface
    {
        if (!$responseType instanceof PayloadResponse) {
            throw new \InvalidArgumentException(
                \sprintf('Response type should be an instance of %s', PayloadResponse::class)
            );
        }

        $converter = new ArrayToXml($responseType->getPayload(), '', false);
        $responseContent = $this->prettify ? $this->prettify($converter) : $this->asSingleLine($converter);

        $response = $this->getResponse($responseType);
        $response->getBody()->write($responseContent);

        return $response->withHeader('Content-Type', 'application/xml; charset=utf-8');
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
        $xmlLines = \explode("\n", $converter->toXml());
        \array_walk(
            $xmlLines,
            function (string $xmlLine): string {
                return \ltrim($xmlLine);
            }
        );

        return \implode('', $xmlLines);
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
        $xmlContent = $domDocument->saveXML();

        return $xmlContent !== false ? \rtrim($xmlContent, "\n") : '';
    }
}
