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

namespace Jgut\Slim\Routing\Response;

use Psr\Http\Message\ServerRequestInterface;

final class RedirectResponse extends AbstractResponse
{
    private function __construct(
        private string $location,
        private int $status,
        ServerRequestInterface $request,
        /**
         * @var array<string, int|float|string|null>
         */
        private array $data = [],
        /**
         * @var array<string, int|float|string|null>
         */
        private array $queryParams = [],
    ) {
        parent::__construct($request);
    }

    /**
     * @param array<string, int|float|string|null> $data
     * @param array<string, int|float|string|null> $queryParams
     */
    public static function movedPermanently(
        string $location,
        ServerRequestInterface $request,
        array $data = [],
        array $queryParams = [],
    ): self {
        return new self($location, 301, $request, $data, $queryParams);
    }

    /**
     * @param array<string, int|float|string|null> $data
     * @param array<string, int|float|string|null> $queryParams
     */
    public static function found(
        string $location,
        ServerRequestInterface $request,
        array $data = [],
        array $queryParams = [],
    ): self {
        return new self($location, 302, $request, $data, $queryParams);
    }

    /**
     * @param array<string, int|float|string|null> $data
     * @param array<string, int|float|string|null> $queryParams
     */
    public static function seeOther(
        string $location,
        ServerRequestInterface $request,
        array $data = [],
        array $queryParams = [],
    ): self {
        return new self($location, 303, $request, $data, $queryParams);
    }

    public static function notModified(ServerRequestInterface $request): self
    {
        return new self('', 304, $request);
    }

    /**
     * @param array<string, int|float|string|null> $data
     * @param array<string, int|float|string|null> $queryParams
     */
    public static function temporaryRedirect(
        string $location,
        ServerRequestInterface $request,
        array $data = [],
        array $queryParams = [],
    ): self {
        return new self($location, 307, $request, $data, $queryParams);
    }

    /**
     * @param array<string, int|float|string|null> $data
     * @param array<string, int|float|string|null> $queryParams
     */
    public static function permanentRedirect(
        string $location,
        ServerRequestInterface $request,
        array $data = [],
        array $queryParams = [],
    ): self {
        return new self($location, 308, $request, $data, $queryParams);
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, int|float|string|null>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, int|float|string|null>
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
}
