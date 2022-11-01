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
use ReflectionClass;
use Reflector;

/**
 * Annotation driver.
 */
class AnnotationDriver extends AbstractAnnotationDriver
{
    use ClassDriverTrait;

    protected function getAnnotation(Reflector $what, string $attribute)
    {
        if ($what instanceof ReflectionClass) {
            return $this->annotationReader->getClassAnnotation($what, $attribute);
        }
        if ($what instanceof \ReflectionMethod) {
            return $this->annotationReader->getMethodAnnotation($what, $attribute);
        }
    }
}
