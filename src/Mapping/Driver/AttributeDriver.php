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
use Jgut\Slim\Routing\Mapping\Annotation\Group as GroupAnnotation;
use Jgut\Slim\Routing\Mapping\Annotation\Route as RouteAnnotation;
use Jgut\Slim\Routing\Mapping\Annotation\Router as RouterAnnotation;

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

    protected function getAnnotation(
        \ReflectionMethod|\ReflectionClass $what,
        string $attribute
    ): GroupAnnotation|RouterAnnotation|RouteAnnotation|null {
        try {
            $classes = $what->getAttributes($attribute, \ReflectionAttribute::IS_INSTANCEOF);
            if (!empty($classes)) {
                return $classes[0]->newInstance();
            }
        } catch (\ReflectionException $e) {
            echo $e->getMessage();
            return null;
        }

        return null;
    }
}
