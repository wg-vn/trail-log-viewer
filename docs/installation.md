# Installing Trail Log Viewer

## Prerequisites

Ensure your environment satisfies the following minimum requirements:

- **PHP 8.0** or higher
- **Laravel 8.0** or higher

---

## Installation via Composer

Install the package into your Laravel application using Composer:

```bash
composer require wg-vn/trail-log-viewer
```

Once installed, the package is ready immediately. You can visit the viewer in your browser at:

```
http://your-app.test/log-viewer
```

*(Note: Assets are served directly from the vendor directory, so running an asset publish command is not required out of the box).*

---

## Publishing the Configuration File

To customize log file locations, routes, middleware, authorization gates, or caching drivers, publish the configuration file:

```bash
php artisan vendor:publish --tag="log-viewer-config"
```

The published configuration will be located at `config/log-viewer.php`.

---

## Publishing Front-End Assets (Optional)

If your web server configuration requires static assets to be served directly from your public directory, you can optionally publish the front-end assets:

```bash
php artisan vendor:publish --tag="log-viewer-assets"
# or
php artisan log-viewer:publish
```

---

## Publishing Views (Optional)

If you need to inject custom JavaScript headers, analytics, or modify the wrapper template:

```bash
php artisan vendor:publish --tag="log-viewer-views"
```

The view template will be placed at `resources/views/vendor/log-viewer/index.blade.php`.

---

## Deploying to Production

By default, Trail Log Viewer restricts access in `production` environments to avoid exposing sensitive logs.

To authorize production access, define the `viewLogViewer` authorization gate or register an authorization callback using `LogViewer::auth()` in your `AppServiceProvider` or `AuthServiceProvider`:

```php
use WgVn\TrailLogViewer\Facades\LogViewer;

public function boot(): void
{
    LogViewer::auth(function ($request) {
        return $request->user() && $request->user()->is_admin;
    });
}
```

For more details on securing your installation, see [Authorizing Access to the Log Viewer](./configuration/access-to-log-viewer.md).
