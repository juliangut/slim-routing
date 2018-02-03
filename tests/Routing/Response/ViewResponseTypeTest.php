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

namespace Jgut\Slim\Routing\Tests\Response;

use Jgut\Slim\Routing\Response\ViewResponseType;
use PHPUnit\Framework\TestCase;

/**
 * Generic view renderer response type tests.
 */
class ViewResponseTypeTest extends TestCase
{
    /**
     * @var ViewResponseType
     */
    protected $responseType;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->responseType = new ViewResponseType();
    }

    public function testTemplate()
    {
        self::assertEquals('', $this->responseType->getTemplate());

        $this->responseType->setTemplate('template_name');

        self::assertEquals('template_name', $this->responseType->getTemplate());
    }

    public function testParameters()
    {
        self::assertEquals([], $this->responseType->getParameters());

        $parameters = ['parameter' => 'value'];

        $this->responseType->setParameters($parameters);

        self::assertEquals($parameters, $this->responseType->getParameters());
    }
}
