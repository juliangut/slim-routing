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
 * Transformer annotation trait.
 */
trait TransformerTrait
{
    /**
     * @var list<non-empty-string>
     */
    protected array $transformers = [];

    /**
     * @var array<string, string>
     */
    protected array $parameters = [];

    /**
     * @return list<non-empty-string>
     */
    public function getTransformers(): array
    {
        return $this->transformers;
    }

    /**
     * @param non-empty-string|list<non-empty-string> $transformers
     */
    public function setTransformers(string|array $transformers): self
    {
        if (\is_string($transformers)) {
            $transformers = [$transformers];
        }

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
