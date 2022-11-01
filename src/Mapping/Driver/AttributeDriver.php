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

namespace Jgut\Slim\Routing\Mapping\Driver;

use Jgut\Mapping\Driver\AbstractClassDriver;
use Reflector;

/**
 * Attribute driver.
 */
class AttributeDriver extends AbstractClassDriver
{
    use ClassDriverTrait;

    /**
     * @param array<string> $paths
     */
    public function __construct(array $paths)
    {
        if (version_compare(\PHP_VERSION, '8.0.0') < 0) {
            @trigger_error('Attribute usage is not supported. Use annotations instead.', \E_USER_DEPRECATED);
        }

        parent::__construct($paths);
    }

    protected function getAnnotation(Reflector $what, string $attribute)
    {
        $attribute = $what->getAttributes($attribute, \ReflectionAttribute::IS_INSTANCEOF);
        if (!empty($attribute)) {
            return $attribute[0]->newInstance();
        }
        return null;
    }
}
