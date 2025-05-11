<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Mapping\Attribute;

use Attribute;
use Jgut\Slim\Routing\Transformer\ParameterTransformer;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Transformer
{
    public function __construct(
        /**
         * @var class-string<ParameterTransformer>
         */
        protected string $transformer,
        /**
         * @var array<string, string>
         */
        private array $parameters = [],
    ) {}

    /**
     * @return class-string<ParameterTransformer>
     */
    public function getTransformer(): string
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
