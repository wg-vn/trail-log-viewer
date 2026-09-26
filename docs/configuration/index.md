# Configuration Overview

Trail Log Viewer is configured via the `config/log-viewer.php` configuration file. If you haven't published it yet, run:

```bash
php artisan vendor:publish --tag="log-viewer-config"
```

---

## Configuration Reference Summary

| Option | Environment Variable | Default | Description |
|---|---|---|---|
| `enabled` | `LOG_VIEWER_ENABLED` | `true` | Master switch to enable or disable the viewer entirely. |
| `api_only` | `LOG_VIEWER_API_ONLY` | `false` | When true, only API routes are registered (disables UI). |
| `route_domain` | `LOG_VIEWER_ROUTE_DOMAIN` | `null` | Subdomain or host where the viewer route should bind. |
| `route_path` | `LOG_VIEWER_ROUTE_PATH` | `'log-viewer'` | URL prefix path for the viewer. |
| `back_to_system_url` | `LOG_VIEWER_BACK_TO_SYSTEM_URL` | `config('app.url')` | URL of the "Back to App" navigation link. |
| `back_to_system_label` | `LOG_VIEWER_BACK_TO_SYSTEM_LABEL` | `null` | Label text for the "Back to App" link. |
| `timezone` | `LOG_VIEWER_TIMEZONE` | `null` | Timezone used to display timestamps (defaults to app timezone). |
| `middleware` | — | `['web', AuthorizeLogViewer::class]` | Middleware stack for web UI routes. |
| `api_middleware` | — | `[EnsureFrontendRequestsAreStateful::class, AuthorizeLogViewer::class]` | Middleware stack for API endpoints. |
| `hosts` | — | `['local' => [...]]` | Array of local and remote host definitions. |
| `include_files` | — | `['*.log', '**/*.log']` | File glob patterns to scan for logs. |
| `exclude_files` | — | `[]` | File glob patterns to ignore. |
| `hide_unknown_files` | — | `true` | Hide log files that do not match supported log types. |
| `cache_driver` | `LOG_VIEWER_CACHE_DRIVER` | `null` | Cache store for indices (defaults to app cache). |
| `cache_key_prefix` | — | `'lv'` | Cache key prefix for index chunking. |
| `chunk_size` | — | `500` | Number of log records stored per index cache chunk. |
| `max_log_size_in_mb` | — | `100` | Maximum single-file size to index in full. |
| `patterns` | — | `[...]` | Custom log type class registrations. |

---

## Dedicated Configuration Guides

- [Route & Domain](./route-and-domain.md)
- [Middleware Stack](./middleware.md)
- [Including & Excluding Files](./including-excluding-files.md)
- [Access to the Log Viewer](./access-to-log-viewer.md)
- [Access to Log Files](./access-to-log-files.md)
- [Result Caching & Performance](./result-caching.md)
- [Multiple Remote Hosts](./multiple-hosts.md)
- [Other Settings](./other.md)
