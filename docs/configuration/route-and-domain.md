# Route and Domain Configuration

All routing settings are configured in `config/log-viewer.php`.

---

## Route Path

By default, Trail Log Viewer registers its web UI and API at the `/log-viewer` route prefix:

```php
'route_path' => env('LOG_VIEWER_ROUTE_PATH', 'log-viewer'),
```

You can change this prefix to any URI segment:

```env
LOG_VIEWER_ROUTE_PATH=admin/system-logs
```

Your log viewer will now be accessible at:
```
https://your-app.test/admin/system-logs
```

---

## Custom Subdomain or Domain

If you prefer to host Trail Log Viewer on a dedicated administration domain or subdomain (e.g., `logs.your-app.com`), specify the `route_domain` option:

```php
'route_domain' => env('LOG_VIEWER_ROUTE_DOMAIN', null),
```

In your `.env`:

```env
LOG_VIEWER_ROUTE_DOMAIN=logs.your-app.com
```

When set, the route group binds strictly to that domain.

---

## "Back to Application" Link

Inside the UI header, a convenient "Back to Laravel" button allows users to navigate back to your main application.

You can configure its destination and label in `config/log-viewer.php`:

```php
'back_to_system_url' => env('LOG_VIEWER_BACK_TO_SYSTEM_URL', config('app.url', null)),
'back_to_system_label' => env('LOG_VIEWER_BACK_TO_SYSTEM_LABEL', null),
```

Or via environment variables:

```env
LOG_VIEWER_BACK_TO_SYSTEM_URL=https://your-app.test/admin/dashboard
LOG_VIEWER_BACK_TO_SYSTEM_LABEL="Return to Admin Dashboard"
```

To remove the button completely, set `LOG_VIEWER_BACK_TO_SYSTEM_URL=false`.

---

## API-Only Mode

If you are using Trail Log Viewer solely as an API backend (e.g., aggregating logs to an external dashboard or monitoring worker), you can disable the web UI while keeping API routes active:

```env
LOG_VIEWER_API_ONLY=true
```
