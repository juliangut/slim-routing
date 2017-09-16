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
 * Abstract base annotation.
 */
abstract class AbstractAnnotation
{
    /**
     * Seed parameters.
     *
     * @param array $parameters
     *
     * @throws \InvalidArgumentException
     */
    protected function seedParameters(array $parameters)
    {
        $configs = array_keys(get_object_vars($this));

        $unknownParameters = array_diff(array_keys($parameters), $configs);
        if (count($unknownParameters)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The following annotation parameters are not recognized: %s',
                    implode(', ', $unknownParameters)
                )
            );
        }

        foreach ($configs as $config) {
            if (isset($parameters[$config])) {
                $callback = [$this, 'set' . ucfirst($config)];

                call_user_func($callback, $parameters[$config]);
            }
        }
    }
}
