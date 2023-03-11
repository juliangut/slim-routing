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

use Jgut\Slim\Routing\Strategy\RequestResponseNamedArgs;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 */
class RequestResponseNamedArgsTest extends TestCase
{
    public function testDispatch(): void
    {
        if (\PHP_VERSION_ID < 80_000) {
            static::markTestSkipped('Named arguments supported from PHP 8.0');
        }

        $responseFactory = new ResponseFactory();
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/');
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $strategy = new RequestResponseNamedArgs([], $responseFactory, $container);

        $callback = static function (
            ServerRequestInterface $request,
            ResponseInterface $response,
            $namedParameter
        ) use ($responseFactory) {
            static::assertEquals('value', $namedParameter);

            $response = $responseFactory->createResponse();
            $response->getBody()
                ->write('Return content');

            return $response;
        };

        $return = $strategy($callback, $request, $response, ['namedParameter' => 'value']);

        static::assertEquals('Return content', (string) $return->getBody());
    }
}
