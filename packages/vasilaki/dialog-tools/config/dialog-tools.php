<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default preview length
    |--------------------------------------------------------------------------
    |
    | Maximum number of characters returned by MessagePreviewer::preview()
    | when no explicit limit is provided.
    |
    */
    'limit' => (int) env('DIALOG_TOOLS_PREVIEW_LIMIT', 80),

    /*
    |--------------------------------------------------------------------------
    | Ellipsis suffix
    |--------------------------------------------------------------------------
    |
    | Appended to a preview whenever the original text was truncated.
    |
    */
    'ellipsis' => env('DIALOG_TOOLS_ELLIPSIS', '...'),
];
