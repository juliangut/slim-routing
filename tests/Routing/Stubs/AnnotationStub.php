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

use Jgut\Slim\Routing\Mapping\Annotation\AbstractAnnotation;

/**
 * Abstract annotation stub.
 */
class AnnotationStub extends AbstractAnnotation
{
    /**
     * @var string
     */
    protected $silly;

    /**
     * Annotation constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->seedParameters($parameters);
    }

    /**
     * @return string
     */
    public function getSilly(): string
    {
        return $this->silly;
    }

    /**
     * @param string $silly
     */
    public function setSilly(string $silly)
    {
        $this->silly = $silly;
    }
}
