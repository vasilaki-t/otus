<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

/**
 * Central OpenAPI document metadata and shared security scheme.
 *
 * The bearer token is issued by Laravel Passport (OAuth2). Clients obtain a
 * token from POST /oauth/token (password or client_credentials grant) and send
 * it as "Authorization: Bearer <token>" to the /api/v1 endpoints.
 */
#[OA\Info(
    version: '1.0.0',
    title: 'OTUS Dialogs API',
    description: 'Versioned REST API for managing dialogs of external systems. '.
        'Authentication is handled by Laravel Passport (OAuth2 bearer tokens).',
)]
#[OA\Server(url: '/', description: 'Current host')]
#[OA\SecurityScheme(
    securityScheme: 'passport',
    type: 'oauth2',
    description: 'OAuth2 bearer token issued by Laravel Passport.',
    flows: [
        new OA\Flow(
            flow: 'password',
            tokenUrl: '/oauth/token',
            refreshUrl: '/oauth/token',
            scopes: [],
        ),
    ],
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Send the Passport access token as "Authorization: Bearer <token>".',
)]
#[OA\Tag(name: 'Dialogs', description: 'CRUD operations for dialogs (external system tasks).')]
class OpenApi {}
