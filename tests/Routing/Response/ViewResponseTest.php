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

use Jgut\Slim\Routing\Response\ViewResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Generic view renderer response tests.
 */
class ViewResponseTest extends TestCase
{
    public function testResponseType(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        /* @var ServerRequestInterface $request */

        $responseType = new ViewResponse('template_name', ['parameter' => 'value'], $request);

        static::assertEquals('template_name', $responseType->getTemplate());
        static::assertEquals(['parameter' => 'value'], $responseType->getParameters());
    }
}
