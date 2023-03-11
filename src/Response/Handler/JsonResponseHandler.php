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

class JsonResponseHandler extends AbstractResponseHandler
{
    protected int $jsonFlags;

    public function __construct(ResponseFactoryInterface $responseFactory, bool $prettify = false)
    {
        parent::__construct($responseFactory);

        $jsonFlags = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION;
        if ($prettify) {
            $jsonFlags |= \JSON_PRETTY_PRINT;
        }

        $this->jsonFlags = $jsonFlags;
    }

    public function handle(ResponseType $responseType): ResponseInterface
    {
        if (!$responseType instanceof PayloadResponse) {
            throw new InvalidArgumentException(
                sprintf('Response type should be an instance of %s.', PayloadResponse::class),
            );
        }

        $payload = $responseType->getPayload();
        if (!$this->isJsonEncodable($payload)) {
            throw new InvalidArgumentException('Response type payload is not json encodable.');
        }

        $response = $this->getResponse($responseType);
        $response->getBody()
            ->write((string) json_encode($payload, $this->jsonFlags));

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    /**
     * @phpstan-param mixed $payload
     */
    protected function isJsonEncodable($payload): bool
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
