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

use Jgut\Slim\Routing\Mapping\Driver\DriverInterface;
use Jgut\Slim\Routing\Naming\NamingInterface;
use Jgut\Slim\Routing\Naming\SnakeCase;
use Jgut\Slim\Routing\Response\Handler\ResponseTypeHandlerInterface;

/**
 * Routing configuration.
 */
class Configuration
{
    /**
     * Routing sources.
     *
     * @var array
     */
    protected $sources = [];

    /**
     * Placeholder aliases.
     *
     * @var array
     */
    protected $placeholderAliases = [
        'numeric' => '\d+',
        'alpha' => '[a-zA-Z]+',
        'alnum' => '[a-zA-Z0-9]+',
        'any' => '.+',
    ];

    /**
     * Naming strategy.
     *
     * @var NamingInterface
     */
    protected $namingStrategy;

    /**
     * Response handlers.
     *
     * @var ResponseTypeHandlerInterface|string[]
     */
    protected $responseHandlers = [];

    /**
     * Configuration constructor.
     *
     * @param array|\Traversable $configurations
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($configurations = [])
    {
        if (!is_iterable($configurations)) {
            throw new \InvalidArgumentException('Configurations must be an iterable');
        }

        $configs = array_keys(get_object_vars($this));

        foreach ($configs as $config) {
            if (isset($configurations[$config])) {
                switch ($config) {
                    case 'sources':
                        $this->setSources($configurations[$config]);
                        break;

                    case 'placeholderAliases':
                        $this->addPlaceholderAliases($configurations[$config]);
                        break;

                    case 'namingStrategy':
                        $this->setNamingStrategy($configurations[$config]);
                        break;

                    case 'responseHandlers':
                        $this->addResponseHandlers($configurations[$config]);
                        break;
                }
            }
        }
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
     * @return static
     */
    public function setSources(array $sources): self
    {
        $this->sources = [];

        foreach ($sources as $source) {
            $this->addSource($source);
        }

        return $this;
    }

    /**
     * Add mapping source.
     *
     * @param string|mixed[]|DriverInterface[] $source
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public function addSource($source): self
    {
        if (!is_string($source) && !is_array($source) && !$source instanceof DriverInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Mapping source must be a string, array or %s, %s given',
                DriverInterface::class,
                is_object($source) ? get_class($source) : gettype($source)
            ));
        }

        $this->sources[] = $source;

        return $this;
    }

    /**
     * Get placeholder aliases.
     *
     * @return array
     */
    public function getPlaceholderAliases(): array
    {
        return $this->placeholderAliases;
    }

    /**
     * Add placeholder aliases.
     *
     * @param array $aliases
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public function addPlaceholderAliases(array $aliases): self
    {
        foreach ($aliases as $alias => $patter) {
            $this->addPlaceholderAlias($alias, $patter);
        }

        return $this;
    }

    /**
     * Add placeholder alias.
     *
     * @param string $alias
     * @param string $pattern
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public function addPlaceholderAlias(string $alias, string $pattern): self
    {
        if (@preg_match('~^' . $pattern . '$~', '') === false) {
            throw new \InvalidArgumentException(
                sprintf('Placeholder pattern "%s" is not a valid regex', $pattern)
            );
        }

        $this->placeholderAliases[$alias] = $pattern;

        return $this;
    }

    /**
     * Get naming strategy.
     *
     * @return NamingInterface
     */
    public function getNamingStrategy(): NamingInterface
    {
        if ($this->namingStrategy === null) {
            $this->namingStrategy = new SnakeCase();
        }

        return $this->namingStrategy;
    }

    /**
     * Set naming strategy.
     *
     * @param NamingInterface $namingStrategy
     *
     * @return static
     */
    public function setNamingStrategy(NamingInterface $namingStrategy): self
    {
        $this->namingStrategy = $namingStrategy;

        return $this;
    }

    /**
     * Get response handlers.
     *
     * @return ResponseTypeHandlerInterface|string[]
     */
    public function getResponseHandlers(): array
    {
        return $this->responseHandlers;
    }

    /**
     * Add response handlers.
     *
     * @param array $handlers
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public function addResponseHandlers(array $handlers): self
    {
        foreach ($handlers as $responseType => $responseHandler) {
            $this->addResponseHandler($responseType, $responseHandler);
        }

        return $this;
    }

    /**
     * Add response handler.
     *
     * @param string                              $response
     * @param ResponseTypeHandlerInterface|string $handler
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public function addResponseHandler(string $response, $handler): self
    {
        $this->responseHandlers[$response] = $handler;

        return $this;
    }
}
