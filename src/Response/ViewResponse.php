<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ViewResponse extends AbstractResponse
{
    public function __construct(
        protected string $template,
        /**
         * @var iterable<string, mixed>
         */
        protected iterable $parameters,
        ServerRequestInterface $request,
        ?ResponseInterface $response = null,
    ) {
        parent::__construct($request, $response);
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return iterable<string, mixed>
     */
    public function getParameters(): iterable
    {
        return $this->parameters;
    }
}
