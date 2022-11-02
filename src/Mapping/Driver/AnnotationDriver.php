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

use Jgut\Mapping\Driver\AbstractAnnotationDriver;
use Jgut\Slim\Routing\Mapping\Annotation\Group as GroupAnnotation;
use Jgut\Slim\Routing\Mapping\Annotation\Route as RouteAnnotation;
use Jgut\Slim\Routing\Mapping\Annotation\Router as RouterAnnotation;
use ReflectionClass;

/**
 * Annotation driver.
 */
class AnnotationDriver extends AbstractAnnotationDriver
{
    use ClassDriverTrait;

    protected function getAnnotation(
        \ReflectionMethod|\ReflectionClass $what,
        string $attribute
    ): GroupAnnotation|RouterAnnotation|RouteAnnotation|null {
        if ($what instanceof ReflectionClass) {
            return $this->annotationReader->getClassAnnotation($what, $attribute);
        }

        return $this->annotationReader->getMethodAnnotation($what, $attribute);
    }
}
