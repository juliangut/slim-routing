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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ViewResponse extends AbstractResponse
{
    protected string $template;

    /**
     * @var array<string, mixed>
     */
    protected array $parameters;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        string $template,
        array $parameters,
        ServerRequestInterface $request,
        ?ResponseInterface $response = null
    ) {
        parent::__construct($request, $response);

        $this->template = $template;
        $this->parameters = $parameters;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
