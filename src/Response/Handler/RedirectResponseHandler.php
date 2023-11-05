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
use Jgut\Slim\Routing\Response\RedirectResponse;
use Jgut\Slim\Routing\Response\ResponseType;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteCollectorInterface;

final class RedirectResponseHandler extends AbstractResponseHandler
{
    private const NOT_MODIFIED_STATUS = 304;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        private RouteCollectorInterface $routeCollector,
    ) {
        parent::__construct($responseFactory);
    }

    public function handle(ResponseType $responseType): ResponseInterface
    {
        if (!$responseType instanceof RedirectResponse) {
            throw new InvalidArgumentException(
                sprintf('Response type should be an instance of %s.', RedirectResponse::class),
            );
        }

        if ($responseType->getStatus() === self::NOT_MODIFIED_STATUS) {
            return $this->getResponse($responseType)
                ->withStatus(304);
        }

        $location = $responseType->getLocation();
        if (!str_starts_with($location, '/') && filter_var($location, \FILTER_VALIDATE_URL) === false) {
            $location = $this->routeCollector
                ->getRouteParser()
                ->urlFor(
                    $location,
                    array_map(
                        static fn(int|float|string|null $data): string => (string) $data,
                        $responseType->getData(),
                    ),
                    array_map(
                        static fn(int|float|string|null $param): string => (string) $param,
                        $responseType->getQueryParams(),
                    ),
                );
        }

        return $this->getResponse($responseType)
            ->withStatus($responseType->getStatus())
            ->withHeader('Location', $location);
    }
}
