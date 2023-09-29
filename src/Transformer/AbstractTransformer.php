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

                    if (\in_array($type, ['string', 'int', 'float', 'bool'], true)) {
                        $parameter = $this->transformToPrimitive($parameter, $type);
                    } elseif ($this->supportsTransform($type)) {
                        $parameter = $this->transformParameter($parameter, $type);
                    }
                }
            },
        );

        return $parameters;
    }

    protected function transformToPrimitive(string $parameter, string $type): float|bool|int|string
    {
        return match ($type) {
            'int' => (int) $parameter,
            'float' => (float) $parameter,
            'bool' => \in_array(trim($parameter), ['1', 'on', 'yes', 'true'], true),
            default => $parameter,
        };
    }

    abstract protected function supportsTransform(string $type): bool;

    abstract protected function transformParameter(string $parameter, string $type): mixed;
}
