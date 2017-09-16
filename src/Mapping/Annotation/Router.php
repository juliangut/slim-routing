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
 * Router annotation.
 *
 * @Annotation
 * @Target("CLASS")
 */
class Router extends AbstractAnnotation
{
    /**
     * Router annotation constructor.
     *
     * @param array $parameters
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $parameters)
    {
        $this->seedParameters($parameters);
    }
}
