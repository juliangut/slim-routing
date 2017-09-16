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

namespace Jgut\Slim\Routing\Mapping\Loader;

/**
 * Annotation mapping loader.
 */
class AnnotationLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     *
     * @return string[]
     */
    public function getMappingData(array $loadingPaths): array
    {
        $mappingClasses = [];

        foreach ($loadingPaths as $path) {
            if (is_dir($path)) {
                $mappingClasses[] = $this->loadSourcesFromDirectory($path);
            } elseif (is_file($path)) {
                $mappingClasses[] = [$this->loadSourceFromFile($path)];
            } else {
                throw new \RuntimeException(sprintf('Path "%s" does not exist', $path));
            }
        }

        $mappingClasses = count($mappingClasses) ? array_merge(...$mappingClasses) : [];

        return array_filter(array_unique($mappingClasses));
    }

    /**
     * Load files from directory.
     *
     * @param string $directory
     *
     * @return string[]
     */
    protected function loadSourcesFromDirectory(string $directory): array
    {
        $mappingClasses = [];

        $recursiveIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        $regexIterator = new \RegexIterator($recursiveIterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($regexIterator as $file) {
            $mappingClasses[] = $this->loadSourceFromFile($file[0]);
        }
        sort($mappingClasses);

        return $mappingClasses;
    }

    /**
     * Load fully qualified class name from file.
     *
     * @param string $file
     *
     * @return string
     *
     * @SuppressWarnings(PMD.CyclomaticComplexity)
     * @SuppressWarnings(PMD.NPathComplexity)
     */
    protected function loadSourceFromFile(string $file): string
    {
        $tokens = token_get_all(file_get_contents($file));
        $hasClass = false;
        $class = null;
        $hasNamespace = false;
        $namespace = '';

        for ($i = 0, $length = count($tokens); $i < $length; $i++) {
            $token = $tokens[$i];

            if (!is_array($token)) {
                continue;
            }

            if ($hasClass && $token[0] === T_STRING) {
                $class = $namespace . '\\' . $token[1];

                break;
            }

            if ($hasNamespace && $token[0] === T_STRING) {
                $namespace = '';

                do {
                    $namespace .= $token[1];

                    $token = $tokens[++$i];
                } while ($i < $length && is_array($token) && in_array($token[0], [T_NS_SEPARATOR, T_STRING]));

                $hasNamespace = false;
            }

            if ($token[0] == T_CLASS) {
                $hasClass = true;
            }
            if ($token[0] === T_NAMESPACE) {
                $hasNamespace = true;
            }
        }

        return $class ?: '';
    }
}
