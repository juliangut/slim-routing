<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Response;

use Jgut\Slim\Routing\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class RedirectResponseTest extends TestCase
{
    public static function provideRedirectionResponseTypes(): iterable
    {
        $request = ServerRequestFactory::fromGlobals();

        yield [301, RedirectResponse::movedPermanently('https://example.com', $request)];
        yield [302, RedirectResponse::found('https://example.com', $request)];
        yield [303, RedirectResponse::seeOther('https://example.com', $request)];
        yield [304, RedirectResponse::notModified($request)];
        yield [307, RedirectResponse::temporaryRedirect('https://example.com', $request)];
        yield [308, RedirectResponse::permanentRedirect('https://example.com', $request)];
    }

    /**
     * @dataProvider provideRedirectionResponseTypes
     */
    public function testRedirectionResponseTypes($expectedStatus, RedirectResponse $responseType): void
    {
        static::assertEquals($expectedStatus, $responseType->getStatus());
    }
}
