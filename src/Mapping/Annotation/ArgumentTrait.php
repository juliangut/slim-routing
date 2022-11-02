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

namespace Jgut\Slim\Routing\Mapping\Annotation;

/**
 * Argument annotation trait.
 */
trait ArgumentTrait
{
    /**
     * Arguments.
     *
     * @var mixed[]
     */
    protected array $arguments = [];

    /**
     * Get arguments.
     *
     * @return mixed[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Set arguments.
     *
     * @param mixed[] $arguments
     *
     * @return self
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }
}
