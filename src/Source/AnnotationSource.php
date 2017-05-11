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

namespace Jgut\Slim\Routing\Source;

use Jgut\Slim\Routing\Compiler\AnnotationCompiler;
use Jgut\Slim\Routing\Loader\AnnotationLoader;

/**
 * Annotations routing source.
 */
class AnnotationSource extends AbstractSource
{
    /**
     * {@inheritdoc}
     */
    public function getLoaderClass(): string
    {
        return AnnotationLoader::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompilerClass(): string
    {
        return AnnotationCompiler::class;
    }
}
