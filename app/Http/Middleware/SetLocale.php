<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Psr15\SetLocaleMiddleware;
use Closure;
use Illuminate\Http\Request;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response as PsrResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;

/**
 * Laravel adapter that bridges the incoming Illuminate request to PSR-7
 * and delegates locale resolution to the PSR-15 SetLocaleMiddleware.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $psr17 = new Psr17Factory;
        $bridge = new PsrHttpFactory($psr17, $psr17, $psr17, $psr17);

        $psrRequest = $bridge->createRequest($request)
            ->withAttribute('locale', $request->route('locale'));

        $handler = new class implements RequestHandlerInterface
        {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new PsrResponse;
            }
        };

        (new SetLocaleMiddleware)->process($psrRequest, $handler);

        return $next($request);
    }
}
