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

namespace Jgut\Slim\Routing\Mapping\Attribute;

use Attribute;
use Jgut\Slim\Routing\Transformer\ParameterTransformer;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Transformer
{
    /**
     * @var array<string, string>
     */
    private array $parameters;

    /**
     * @param array<string, string>|null $parameters
     */
    public function __construct(
        /**
         * @var class-string<ParameterTransformer>|ParameterTransformer|null
         */
        protected string|object|null $transformer = null,
        ?array $parameters = [],
    ) {
        $this->parameters = $parameters ?? [];
    }

    /**
     * @return class-string<ParameterTransformer>|ParameterTransformer|null
     */
    public function getTransformer(): string|object|null
    {
        return $this->transformer;
    }

    /**
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
