# Other Configuration Settings

---

## Timezone

By default, timestamps are displayed using your application's configured timezone (`config('app.timezone')`).

To enforce a specific display timezone for Trail Log Viewer:

```php
'timezone' => env('LOG_VIEWER_TIMEZONE', 'UTC'),
```

*(Note: Users can also toggle UTC timestamps instantly in the UI using [Display Preferences](../papertrail-features/display-preferences.md)).*

---

## Maximum File Size to Scan

To protect server memory when dealing with massive unindexed files:

```php
'max_log_size_in_mb' => 100,
```

Files larger than this limit will still open, but will scan incrementally rather than loading completely into memory.

---

## Shorter Stack Traces

Trail Log Viewer automatically cleans up lengthy Laravel exception stack traces by collapsing vendor framework frames while keeping application frames highlighted:

```php
'shorter_stack_traces' => true,
```
