# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2026-09-26

### Added
- Auto-display single matched query element: automatically expands the log entry by default when a search query matches exactly one log entry across all files or within a selected file.
- Smart tab focus: automatically activates the Raw tab when a search query matches attributes inside context or raw log text (e.g. error ID, correlation UUID), and activates Stack Trace when matching exception frames.

## [1.1.0] - 2026-09-25

### Added
- Papertrail-style docked bottom toolbar with embedded search actions and live status controls.
- Recent and Saved Searches: query history with relative timestamps and bookmarkable saved search rules in local storage.
- Search syntax reference card modal with interactive query preset insertion.
- Seek to Date or Time: relative jump presets (5m, 15m, 1h, 6h, 24h, Now) and datetime picker with index page resolution.
- Interactive Velocity Graph: SVG event frequency histogram with Rate and Count modes, time windows, and click-to-seek.
- Display Preferences: customizable typography (fonts, sizes), row density (Comfort / Compact), search match highlighting, message truncation, UTC timestamps, and column visibility.
- Search Query Alerts: configurable alert rules with threshold conditions and delivery actions (desktop browser push, in-app toasts, webhooks, email).
- Live Tail Streaming: real-time polling with pulsating status indicator, auto-scroll to newest entries, and smart pause-on-scroll with quick resume banner.
- REST API Velocity Endpoint: `GET /log-viewer/api/logs/velocity` and `seek`, `date_from`, `date_to` parameters on `/log-viewer/api/logs`.
- Comprehensive Documentation Suite: 24 markdown documentation guides under `/docs` (excluded from Composer package distribution archives via `.gitattributes`).

## [1.0.0] - 2026-09-25

### Changed
- Renamed the package to `wg-vn/trail-log-viewer` and the PHP namespace from `Opcodes\LogViewer` to `WgVn\TrailLogViewer`.
- Requires PHP 8.5 and Laravel 12 or 13.
- Replaced Laravel Mix with Vite and upgraded the front-end to Tailwind CSS 4, Pinia 4, Vue Router 5 and VueUse 15. Building the assets requires Node.js 26.
- Replaced the GitHub icon next to the title with the Trail Log Viewer logo.

### Removed
- The "Show your support" links and the `show_support_link` script variable.

### Added
- The sidebar can be collapsed on desktop. The choice is remembered in local storage.
- `composer demo` starts a local demo app with sample logs, no Laravel installation needed.
- GitHub Actions workflow to publish releases to Packagist on tag push.
