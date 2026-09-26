# Default Log Types

Trail Log Viewer includes built-in parsers for standard web and infrastructure logs.

---

## Supported Formats

| Type Identifier | Name | Description |
|---|---|---|
| `laravel` | Laravel Logs | Standard Monolog output from `storage/logs/laravel.log`. |
| `horizon` | Laravel Horizon | Horizon queue worker output. |
| `http_access` | HTTP Access | Standard Apache and Nginx access logs (`combined` and `common` formats). |
| `http_error_apache` | HTTP Error (Apache) | Apache error logs with module and process ID. |
| `http_error_nginx` | HTTP Error (Nginx) | Nginx error logs with worker PID and client IP. |
| `redis` | Redis Logs | Redis server output. |
| `postgres` | Postgres Logs | PostgreSQL database logs. |
| `supervisor` | Supervisor Logs | Supervisord process manager logs. |
| `php_fpm` | PHP-FPM Logs | PHP FastCGI Process Manager logs. |

---

## Automatic Type Detection

When opening a log file, Trail Log Viewer reads the first few lines to automatically detect and assign the appropriate parser.
