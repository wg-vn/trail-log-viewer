# REST API Overview

Trail Log Viewer provides a comprehensive REST API used by its Vue front-end and accessible for automated monitoring, CI/CD health checks, and integrations.

---

## Base URL

All API routes are prefixed by your configured `route_path` and `api`:

```
GET /log-viewer/api/*
```

---

## Authentication & Security

API routes are protected by the `api_middleware` stack defined in `config/log-viewer.php`.

For browser-based calls, stateful cookie session authentication (`EnsureFrontendRequestsAreStateful`) is validated.

For external API clients:
1. Provide an authorization header:
   ```http
   Authorization: Bearer <token>
   ```
2. Or use Laravel Gates as configured in [Authorizing Access](../configuration/access-to-log-viewer.md).

---

## API Endpoints

- [Logs API](./logs.md) — Query logs, seek to timestamp, and fetch velocity histograms.
- [Folders API](./folders.md) — List folders, download ZIPs, and clear folder cache.
- [Files API](./files.md) — List files, clear index cache, and delete logs.
- [Hosts API](./hosts.md) — List configured multi-host servers.
