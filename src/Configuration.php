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

namespace Jgut\Slim\Routing;

/**
 * Routing configuration.
 */
class Configuration
{
    /**
     * @var string
     */
    protected $compilationPath;

    /**
     * Routing sources.
     *
     * @var array
     */
    protected $sources = [];

    /**
     * Configuration constructor.
     *
     * @param array|\Traversable $configurations
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($configurations = [])
    {
        if (!is_array($configurations) && !$configurations instanceof \Traversable) {
            throw new \InvalidArgumentException('Configurations must be a traversable');
        }

        $this->seedConfigurations($configurations);
    }

    /**
     * Seed configurations.
     *
     * @param array|\Traversable $configurations
     */
    protected function seedConfigurations($configurations)
    {
        $configs = array_keys(get_object_vars($this));

        foreach ($configs as $config) {
            if (isset($configurations[$config])) {
                $callback = [$this, 'set' . ucfirst($config)];

                call_user_func($callback, $configurations[$config]);
            }
        }
    }

    /**
     * Get compilation path.
     *
     * @return string|null
     */
    public function getCompilationPath()
    {
        return $this->compilationPath;
    }

    /**
     * Set compilation path.
     *
     * @param string $compilationPath
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function setCompilationPath(string $compilationPath)
    {
        if (!file_exists($compilationPath) || !is_dir($compilationPath) || !is_writable($compilationPath)) {
            throw new \RuntimeException(sprintf('%s directory does not exist or is write protected', $compilationPath));
        }

        $this->compilationPath = $compilationPath;

        return $this;
    }

    /**
     * Get routing paths.
     *
     * @return array
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * Set routing paths.
     *
     * @param array $sources
     *
     * @return $this
     */
    public function setSources(array $sources)
    {
        $this->sources = $sources;

        return $this;
    }
}
