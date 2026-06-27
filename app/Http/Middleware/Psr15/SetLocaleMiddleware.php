<?php

namespace App\Http\Middleware\Psr15;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-15 middleware that resolves the application locale from the request.
 *
 * Locale is taken either from the PSR-7 request attribute "locale"
 * (set by the routing layer) or from the first path segment of the URI.
 * If the resolved value is not in the supported list, the default is used.
 */
class SetLocaleMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $supported = (array) config('locales.supported', []);
        $default = (string) config('locales.default', 'en');

        $locale = $request->getAttribute('locale');

        if (! is_string($locale) || $locale === '') {
            $segments = explode('/', trim($request->getUri()->getPath(), '/'));
            $locale = $segments[0];
        }

        if (! in_array($locale, $supported, true)) {
            $locale = $default;
        }

        app()->setLocale($locale);

        return $handler->handle($request);
    }
}
