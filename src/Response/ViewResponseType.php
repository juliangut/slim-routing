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

/**
 * Generic view renderer response type.
 */
class ViewResponseType extends AbstractResponseType
{
    /**
     * Template name.
     *
     * @var string
     */
    protected $template = '';

    /**
     * Template parameters.
     *
     * @var array
     */
    protected $parameters = [];

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
     * Set template name.
     *
     * @param string $template
     *
     * @return static
     */
    public function setTemplate(string $template): ViewResponseType
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Set template parameters.
     *
     * @param array $parameters
     *
     * @return static
     */
    public function setParameters(array $parameters): ViewResponseType
    {
        $this->parameters = $parameters;

        return $this;
    }
}
