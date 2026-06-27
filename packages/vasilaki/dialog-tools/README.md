# vasilaki/dialog-tools

Small Laravel package with helpers for working with chat dialog messages:
building short, word-aware previews of request/response texts and generating
slugs for them.

## Installation (local path repository)

This package is consumed locally from the host application via a Composer
`path` repository. In the root `composer.json`:

```json
"repositories": [
    { "type": "path", "url": "packages/vasilaki/dialog-tools", "options": { "symlink": true } }
],
"require": {
    "vasilaki/dialog-tools": "*"
}
```

```bash
composer update vasilaki/dialog-tools
```

The service provider `Vasilaki\DialogTools\DialogToolsServiceProvider` is
registered automatically through Laravel package auto-discovery.

## Usage

```php
use Vasilaki\DialogTools\Contracts\MessagePreviewer;

$previewer = app(MessagePreviewer::class);
$previewer->preview('A very long message text...', 40); // word-aware preview
$previewer->slug('A very long message text');            // a-very-long-message-text
```

Via the facade:

```php
use Vasilaki\DialogTools\Facades\DialogTools;

DialogTools::preview($text);
```

## Binding

`DialogToolsServiceProvider::register()` binds the
`Vasilaki\DialogTools\Contracts\MessagePreviewer` contract to the
`Vasilaki\DialogTools\MessagePreviewerService` implementation, configured from
`config/dialog-tools.php`.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=dialog-tools-config
```

- `limit` — default preview length in characters (default `80`).
- `ellipsis` — suffix appended when text is truncated (default `...`).

## License

MIT
