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

namespace Jgut\Slim\Routing\Tests\Stubs;

use Jgut\Slim\Routing\Compiler\CompilerInterface;
use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Loader\LoaderInterface;
use Jgut\Slim\Routing\Manager as RoutingManager;
use Jgut\Slim\Routing\Source\SourceInterface;

/**
 * Routing manager stub.
 */
class ManagerStub extends RoutingManager
{
    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var CompilerInterface
     */
    protected $compiler;

    /**
     * Manager Stub constructor.
     *
     * @param Configuration     $configuration
     * @param LoaderInterface   $loader
     * @param CompilerInterface $compiler
     */
    public function __construct(
        Configuration $configuration,
        LoaderInterface $loader = null,
        CompilerInterface $compiler = null
    ) {
        parent::__construct($configuration);

        $this->loader = $loader;
        $this->compiler = $compiler;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLoader(SourceInterface $source): LoaderInterface
    {
        parent::getLoader($source);

        return $this->loader;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCompiler(SourceInterface $source): CompilerInterface
    {
        parent::getCompiler($source);

        return $this->compiler;
    }
}
