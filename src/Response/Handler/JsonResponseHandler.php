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

use InvalidArgumentException;
use Jgut\Slim\Routing\Response\PayloadResponse;
use Jgut\Slim\Routing\Response\ResponseType;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class JsonResponseHandler extends AbstractResponseHandler
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

        if (!$this->isJsonEncodable($payload)) {
            throw new InvalidArgumentException('Response type payload is not JSON encodable.');
        }

        $response = $this->getResponse($responseType);
        $response->getBody()
            ->write(json_encode($payload, $this->getJsonEncodeFlags() | \JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    private function getJsonEncodeFlags(): int
    {
        $jsonFlags = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION;
        if ($this->prettify) {
            $jsonFlags |= \JSON_PRETTY_PRINT;
        }

        return $jsonFlags;
    }

    private function isJsonEncodable(mixed $payload): bool
    {
        if (\is_resource($payload)) {
            return false;
        }

        if (\is_array($payload)) {
            foreach ($payload as $data) {
                if (!$this->isJsonEncodable($data)) {
                    return false;
                }
            }
        }

        return true;
    }
}
