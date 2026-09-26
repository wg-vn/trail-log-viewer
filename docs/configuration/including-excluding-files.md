# Including and Excluding Log Files

By default, Trail Log Viewer automatically discovers all `.log` files in Laravel's standard log directory (`storage/logs`). You can customize the discovery rules to include custom directories, system logs, or exclude specific files.

---

## Including Additional Files

Use the `include_files` configuration option to add glob patterns:

```php
'include_files' => [
    '*.log',
    '**/*.log',
    // Absolute paths:
    '/var/log/nginx/*.log',
    '/var/log/apache2/*.log',
    '/var/log/redis/*.log',
],
```

### Pattern Examples

- `'*.log'` — Scans files directly inside `storage/logs/`.
- `'**/*.log'` — Recursively scans nested folders inside `storage/logs/` (e.g. `storage/logs/2026/09/app.log`).
- `'/var/log/nginx/*.log'` — Absolute path to Nginx web server logs.
- `storage_path('logs/worker-*.log')` — Dynamic paths using Laravel helpers.

---

## Excluding Files

Use `exclude_files` to hide sensitive or unwanted log files. Exclude patterns take precedence over include patterns:

```php
'exclude_files' => [
    'secret-audit.log',
    'my_secret_*.log',
    '**/*debug.log',
],
```

---

## Hiding Unknown / Unsupported Files

By default, Trail Log Viewer filters out files that do not match any recognized log driver formats (such as arbitrary text dumps).

To display all files regardless of recognized log structure, set `hide_unknown_files` to `false`:

```php
'hide_unknown_files' => false,
```
