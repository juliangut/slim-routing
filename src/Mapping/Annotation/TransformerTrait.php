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

use Jgut\Slim\Routing\Transformer\ParameterTransformer;

/**
 * Transformer annotation trait.
 */
trait TransformerTrait
{
    /**
     * @var list<class-string<ParameterTransformer>>
     */
    protected array $transformers = [];

    /**
     * @var array<string, string>
     */
    protected array $parameters = [];

    /**
     * @return list<class-string<ParameterTransformer>>
     */
    public function getTransformers(): array
    {
        return $this->transformers;
    }

    /**
     * @param list<class-string<ParameterTransformer>> $transformers
     */
    public function setTransformers(array $transformers): self
    {
        $this->transformers = $transformers;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array<string, string> $parameters
     */
    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }
}
