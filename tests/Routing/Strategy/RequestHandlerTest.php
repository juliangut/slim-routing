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

namespace Jgut\Slim\Routing\Tests\Strategy;

use Jgut\Slim\Routing\Strategy\RequestHandler;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequestFactory;

/**
 * PSR-15 RequestHandler invocation strategy tests.
 */
class RequestHandlerTest extends TestCase
{
    public function testDispatch(): void
    {
        $responseFactory = new ResponseFactory();
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        /* @var ContainerInterface $container */
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/');
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
        /* @var ResponseInterface $response */

        $strategy = new RequestHandler([], $responseFactory, $container, true);

        $callback = function (ServerRequestInterface $request) use ($responseFactory) {
            static::assertEquals('value', $request->getAttribute('param'));

            $response = $responseFactory->createResponse();
            $response->getBody()->write('Return content');

            return $response;
        };

        $return = $strategy($callback, $request, $response, ['param' => 'value']);

        static::assertEquals('Return content', (string) $return->getBody());
    }
}
