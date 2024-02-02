<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Response\Handler;

use InvalidArgumentException;
use Jgut\Slim\Routing\Response\PayloadResponse;
use Jgut\Slim\Routing\Response\ResponseType;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Spatie\ArrayToXml\ArrayToXml;

final class XmlResponseHandler extends AbstractResponseHandler
{
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        private bool $prettify = false,
    ) {
        parent::__construct($responseFactory);
    }

    public function handle(ResponseType $responseType): ResponseInterface
    {
        if (!$responseType instanceof PayloadResponse) {
            throw new InvalidArgumentException(
                sprintf('Response type should be an instance of %s.', PayloadResponse::class),
            );
        }

        $payload = $responseType->getPayload();
        if (is_iterable($payload) && !\is_array($payload)) {
            $payload = iterator_to_array($payload);
        }

        if (!\is_array($payload)) {
            throw new InvalidArgumentException('Response type payload is not XML encodable.');
        }

        $converter = new ArrayToXml($payload, '', false);
        $responseContent = $this->prettify ? $this->getPrettified($converter) : $this->getCompressed($converter);

        $response = $this->getResponse($responseType);
        $response->getBody()
            ->write($responseContent);

        return $response->withHeader('Content-Type', 'application/xml; charset=utf-8');
    }

    /**
     * Return XML in a single line.
     */
    private function getCompressed(ArrayToXml $converter): string
    {
        $xmlLines = explode("\n", $converter->toXml());
        array_walk(
            $xmlLines,
            static fn(string $xmlLine): string => ltrim($xmlLine),
        );

        return implode('', $xmlLines);
    }

    /**
     * Prettify xml output.
     */
    private function getPrettified(ArrayToXml $converter): string
    {
        $domDocument = $converter->toDom();
        $domDocument->formatOutput = true;
        $xmlContent = $domDocument->saveXML();

        return $xmlContent !== false ? rtrim($xmlContent, "\n") : '';
    }
}
