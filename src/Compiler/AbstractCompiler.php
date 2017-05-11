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

namespace Jgut\Slim\Routing\Compiler;

/**
 * Abstract routing compiler.
 */
abstract class AbstractCompiler implements CompilerInterface
{
    /**
     * Check if duplicated placeholders are present on route patterns.
     *
     * @param string $pattern
     * @param array  $placeholders
     *
     * @throws \RuntimeException
     */
    protected function checkPath(string $pattern, array $placeholders)
    {
        if (preg_match_all('/\{([^{]+)\}/', $pattern, $patternPlaceholders)) {
            if (count($patternPlaceholders[1]) !== count(array_unique($patternPlaceholders[1]))) {
                throw new \RuntimeException(
                    sprintf('Pattern "%s" contains duplicated placeholders', $pattern)
                );
            }
        }

        $unknownPlaceholders = array_diff(array_keys($placeholders), $patternPlaceholders[1]);
        if (count($unknownPlaceholders)) {
            throw new \RuntimeException(
                sprintf(
                    'Pattern "%s" does not contain the following placeholders: %s',
                    $pattern,
                    implode(', ', $unknownPlaceholders)
                )
            );
        }
    }
}
