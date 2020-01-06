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

/**
 * Generic view renderer response.
 */
class ViewResponse extends AbstractResponse
{
    /**
     * Template name.
     *
     * @var string
     */
    protected $template;

    /**
     * Template parameters.
     *
     * @var array<string, mixed>
     */
    protected $parameters;

    /**
     * ViewResponseType constructor.
     *
     * @param string                 $template
     * @param array<string, mixed>   $parameters
     * @param ServerRequestInterface $request
     * @param ResponseInterface|null $response
     */
    public function __construct(
        string $template,
        array $parameters,
        ServerRequestInterface $request,
        ResponseInterface $response = null
    ) {
        parent::__construct($request, $response);

        $this->template = $template;
        $this->parameters = $parameters;
    }

    /**
     * Get template name.
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * Get template parameters.
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
