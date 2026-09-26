# Middleware Configuration

Trail Log Viewer isolates its Web UI and REST API into two distinct route groups, each with its own configurable middleware stack.

---

## Web UI Middleware

The `middleware` configuration key defines the pipeline applied to the HTML user interface route:

```php
'middleware' => [
    'web',
    \WgVn\TrailLogViewer\Http\Middleware\AuthorizeLogViewer::class,
],
```

> **Note:** The `'web'` middleware group is required because Trail Log Viewer relies on cookies and session state to authenticate requests and secure API communication.

### Adding Custom Middleware

You can attach additional middleware such as Laravel's built-in authentication or role-checking middleware:

```php
'middleware' => [
    'web',
    'auth',
    'verified',
    \WgVn\TrailLogViewer\Http\Middleware\AuthorizeLogViewer::class,
],
```

Using HTTP Basic Auth:

```php
'middleware' => [
    'web',
    'auth.basic',
    \WgVn\TrailLogViewer\Http\Middleware\AuthorizeLogViewer::class,
],
```

---

## API Middleware

The `api_middleware` configuration key defines the middleware applied to all `/api/*` endpoints (e.g., `/log-viewer/api/logs`):

```php
'api_middleware' => [
    \WgVn\TrailLogViewer\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    \WgVn\TrailLogViewer\Http\Middleware\AuthorizeLogViewer::class,
],
```

`EnsureFrontendRequestsAreStateful` validates that requests originating from the browser UI are authenticated and match your configured `APP_URL` or stateful domains, guarding against CSRF and cross-origin attacks.

### Stateful Domains

If your API is served from a subdomain or different port, ensure your domain is registered in `config/sanctum.php` or `config/log-viewer.php`:

```php
'hosts' => [
    'local' => [
        'name' => env('APP_NAME', 'Local'),
    ],
],
```
