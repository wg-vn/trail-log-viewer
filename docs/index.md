# Trail Log Viewer Documentation

**Trail Log Viewer** (`wg-vn/trail-log-viewer`) is an advanced, high-performance log viewer for [Laravel](https://laravel.com) applications. Built from the ground up for high speed and massive log files, Trail Log Viewer combines indexed multi-file log reading with **Papertrail-style observability features**: an interactive velocity histogram, instant date and time seeking, real-time live tail streaming, custom display preferences, and configurable search query alerts.

---

## Key Features

- **Blazing Fast Indexing:** Custom binary chunk indexing and caching enables instant search, filtering, and pagination across multi-gigabyte log files without memory exhaustion.
- **Docked Papertrail Toolbar:** Bottom-docked search toolbar with quick tools, bookmarking, and live tail controls.
- **Recent & Saved Searches:** History of recent searches with relative timestamps (`1m ago`, `2h ago`) and bookmarked saved searches with custom titles.
- **Search Syntax & Tips:** Rich search grammar supporting exact phrases (`"call stack"`), negation (`-CROND`), boolean combinations (`OR`), and case-insensitive regex patterns (`/fatal|error/i`).
- **Seek to Date or Time:** Relative jump shortcuts (`5m ago`, `1h ago`, `24h ago`, `Now`) and precise datetime-local pickers jump directly to specific log positions.
- **Interactive Velocity Graph:** Collapsible event frequency histogram with **Rate** (events/sec) and **Count** modes, customizable time windows, hover tooltips, and click-to-seek.
- **Display Preferences:** Personalize typography (font family, font size), table density (`comfort` vs `compact`), search match highlighting, UTC timestamps, and column visibility.
- **Search Query Alerts:** Configure in-app notifications, desktop browser push alerts, webhooks, or email notifications triggered during live tail when events exceed defined thresholds.
- **Live Tail Streaming:** Real-time polling with an emerald pulsating indicator, auto-scroll to newest entries, and smart pause-on-scroll with a 1-click jump-to-live banner.
- **Multi-Host Aggregation:** Centralize and view logs across multiple remote servers and environments in a single interface.
- **Extensible Log Types:** Built-in support for Laravel, Laravel Horizon, Nginx & Apache access/error logs, Redis, Supervisor, and Postgres, with an API for registering custom log parsers.

---

## Documentation Contents

### Getting Started
- [Installation](./installation.md) — Prerequisites, Composer installation, and asset publishing.

### Configuration
- [Configuration Overview](./configuration/index.md) — Summary of all configuration options.
- [Route & Domain](./configuration/route-and-domain.md) — Customize the URL path, domain, and back-to-application link.
- [Middleware](./configuration/middleware.md) — Web and API middleware stacks.
- [Access to the Log Viewer](./configuration/access-to-log-viewer.md) — Gates, environment locks, and authorization callbacks.
- [Access to Log Files](./configuration/access-to-log-files.md) — Granular permissions for downloading and deleting files/folders.
- [Including & Excluding Files](./configuration/including-excluding-files.md) — Custom log locations and file glob patterns.
- [Result Caching & Indexing](./configuration/result-caching.md) — Cache stores, index chunks, and cache management.
- [Multiple Hosts](./configuration/multiple-hosts.md) — Connecting remote hosts and server clusters.
- [Other Settings](./configuration/other.md) — Timezones, scan limits, and UI options.

### Papertrail Observability Features
- [Overview & Docked Toolbar](./papertrail-features/overview.md) — The Papertrail layout and tool bar.
- [Recent & Saved Searches](./papertrail-features/searches-and-tips.md) — Search history, bookmarks, and syntax examples.
- [Seek to Date or Time](./papertrail-features/seek.md) — Relative time presets and index page resolution.
- [Velocity Graph](./papertrail-features/velocity-graph.md) — Event frequency histogram, rate/count modes, and click-to-seek.
- [Display Preferences](./papertrail-features/display-preferences.md) — Typography, table density, highlights, and column toggling.
- [Alerts & Live Tail](./papertrail-features/alerts-and-live-tail.md) — Real-time tailing, pause-on-scroll, and query alert triggers.

### Log Types
- [Default Log Types](./log-types/default.md) — Supported log formats and patterns.
- [Custom Log Types](./log-types/custom.md) — Writing and registering custom log format parsers.

### REST API
- [API Overview](./api/index.md) — Endpoints, stateful authentication, and request headers.
- [Logs API](./api/logs.md) — Querying logs, date ranges, seeking, and the velocity histogram endpoint.
- [Folders API](./api/folders.md) — Folder hierarchies, index clearing, and folder downloads.
- [Files API](./api/files.md) — File metadata, clearing indices, file downloads, and deletions.
- [Hosts API](./api/hosts.md) — Multi-host queries and remote server status.

---

## Frequently Asked Questions

### Can I use Trail Log Viewer on an existing Laravel project?
Yes. Trail Log Viewer is completely isolated from your existing application. It registers its own dedicated route (`/log-viewer` by default) and serves bundled assets without interfering with your front-end pipeline.

### What are the system requirements?
- **PHP:** 8.0 or higher
- **Laravel:** 8.0, 9.x, 10.x, 11.x, or higher
- **Browser:** Any modern evergreen browser (Chrome, Firefox, Safari, Edge)

### Are documentation files included when installing the package via Composer?
No. All documentation files located in `/docs` are marked with `export-ignore` in `.gitattributes`, keeping your vendor directory lightweight.
