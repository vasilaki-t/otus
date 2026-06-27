<?php

namespace Tests\Unit;

use App\Http\Middleware\Psr15\SetLocaleMiddleware;
use Nyholm\Psr7\Response as PsrResponse;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tests\TestCase;

/**
 * Unit test for the PSR-15 SetLocaleMiddleware.
 *
 * The middleware is exercised directly through its process() method
 * with a Nyholm PSR-7 request and a dummy handler. No database is used.
 */
class SetLocaleMiddlewareTest extends TestCase
{
    private function dummyHandler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface
        {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new PsrResponse;
            }
        };
    }

    public function test_supported_locale_attribute_is_applied(): void
    {
        $request = (new ServerRequest('GET', '/ru/dashboard'))
            ->withAttribute('locale', 'ru');

        (new SetLocaleMiddleware)->process($request, $this->dummyHandler());

        $this->assertSame('ru', app()->getLocale());
    }

    public function test_unsupported_locale_falls_back_to_default(): void
    {
        $request = (new ServerRequest('GET', '/de/dashboard'))
            ->withAttribute('locale', 'de');

        (new SetLocaleMiddleware)->process($request, $this->dummyHandler());

        $this->assertSame('en', app()->getLocale());
    }

    public function test_locale_is_resolved_from_uri_path_segment(): void
    {
        $request = new ServerRequest('GET', '/ru/dashboard');

        (new SetLocaleMiddleware)->process($request, $this->dummyHandler());

        $this->assertSame('ru', app()->getLocale());
    }
}
