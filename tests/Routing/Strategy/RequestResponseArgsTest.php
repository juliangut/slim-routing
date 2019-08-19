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

use Jgut\Slim\Routing\Strategy\RequestResponseArgs;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Route callback strategy with route parameters as individual arguments tests.
 */
class RequestResponseArgsTest extends TestCase
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

        $strategy = new RequestResponseArgs([], $responseFactory, $container);

        $callback = function (
            ServerRequestInterface $request,
            ResponseInterface $response,
            $param
        ) use ($responseFactory) {
            static::assertEquals('value', $param);

            $response = $responseFactory->createResponse();
            $response->getBody()->write('Return content');

            return $response;
        };

        $return = $strategy($callback, $request, $response, ['param' => 'value']);

        static::assertEquals('Return content', (string) $return->getBody());
    }
}
