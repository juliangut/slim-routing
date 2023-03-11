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

namespace Jgut\Slim\Routing\Transformer;

abstract class AbstractTransformer implements ParameterTransformer
{
    public function transform(array $parameters, array $definitions): array
    {
        array_walk(
            $parameters,
            function (&$parameter, string $name) use ($definitions): void {
                if (\array_key_exists($name, $definitions)) {
                    $type = $definitions[$name];
                    $parameter = \in_array($type, ['string', 'int', 'float', 'bool'], true)
                        ? $this->transformToPrimitive($parameter, $type)
                        : $this->transformParameter($parameter, $type);
                }
            },
        );

        return $parameters;
    }

    /**
     * @return bool|float|int|string
     */
    protected function transformToPrimitive(string $parameter, string $type)
    {
        switch ($type) {
            case 'int':
                $transformedParameter = (int) $parameter;
                break;

            case 'float':
                $transformedParameter = (float) $parameter;
                break;

            case 'bool':
                $transformedParameter = \in_array(trim($parameter), ['1', 'on', 'yes', 'true'], true);
                break;

            default:
                $transformedParameter = $parameter;
        }

        return $transformedParameter;
    }

    /**
     * Transform parameter.
     *
     * @phpstan-return mixed
     */
    abstract protected function transformParameter(string $parameter, string $type);
}
