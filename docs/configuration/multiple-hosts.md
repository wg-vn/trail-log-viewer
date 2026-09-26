# Multi-Host Log Aggregation

Trail Log Viewer can aggregate and query logs across multiple servers or staging/production environments from a single dashboard.

---

## Configuration

In `config/log-viewer.php`, configure the `hosts` array:

```php
'hosts' => [
    'local' => [
        'name' => 'Local Environment',
    ],
    'production-web-1' => [
        'name' => 'Production Web #1',
        'host' => 'https://web1.example.com/log-viewer',
        'authorization' => 'Bearer ' . env('LOG_VIEWER_WEB1_TOKEN'),
        'headers' => [
            'X-Custom-Header' => 'value',
        ],
    ],
    'production-web-2' => [
        'name' => 'Production Web #2',
        'host' => 'https://web2.example.com/log-viewer',
        'authorization' => 'Bearer ' . env('LOG_VIEWER_WEB2_TOKEN'),
    ],
],
```

---

## Host Options

- **`name`** — Display name rendered in the host selector dropdown.
- **`host`** — Root URL to the Trail Log Viewer instance on the remote server.
- **`authorization`** — Authorization header sent with proxied requests (e.g. `Bearer <token>`).
- **`headers`** — Key-value array of custom headers passed to the remote instance.

---

## How It Works

When a remote host is selected in the UI:
1. The local Trail Log Viewer acts as a secure API reverse-proxy.
2. Requests are forwarded directly to the remote server using Laravel's HTTP Client (`Http::withHeaders()`).
3. Results, velocity charts, and log content are seamlessly returned to your browser.
