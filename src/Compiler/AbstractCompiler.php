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

use Jgut\Slim\Routing\Configuration;

/**
 * Abstract routing compiler.
 */
abstract class AbstractCompiler implements CompilerInterface
{
    /**
     * Routing configuration.
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     * AbstractCompiler constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

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

    /**
     * Get placeholder pattern.
     *
     * @param string $pattern
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function getPlaceholderPattern(string $pattern): string
    {
        $aliases = $this->configuration->getPlaceholderAliases();

        if (array_key_exists($pattern, $aliases)) {
            return $aliases[$pattern];
        }

        if (@preg_match('/' . $pattern . '/', '') !== false) {
            return $pattern;
        }

        throw new \InvalidArgumentException(
            sprintf('Placeholder pattern "%s" is not a known alias or a valid regex', $pattern)
        );
    }
}
