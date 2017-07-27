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

namespace Jgut\Slim\Routing\Loader;

/**
 * Classes routing loader.
 */
class AnnotationLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function load(array $loadingPaths): array
    {
        $routingFiles = [];

        foreach ($loadingPaths as $path) {
            if (is_dir($path)) {
                $routingFiles[] = $this->loadFromDirectory($path);
            } elseif (is_file($path)) {
                $routingFiles[] = [$this->loadFromFile($path)];
            } else {
                throw new \RuntimeException(sprintf('Path "%s" does not exist', $path));
            }
        }

        $routingFiles = count($routingFiles) ? array_merge(...$routingFiles) : [];

        return array_filter(array_unique($routingFiles));
    }

    /**
     * Load files from directory.
     *
     * @param string $directory
     *
     * @return string[]
     */
    protected function loadFromDirectory(string $directory): array
    {
        $routingFiles = [];

        foreach (glob($directory . '/{**/*,*}.php', GLOB_BRACE | GLOB_ERR) as $file) {
            if (is_file($file)) {
                $routingFiles[] = $this->loadFromFile($file);
            }
        }

        return $routingFiles;
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
    protected function loadFromFile(string $file): string
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
