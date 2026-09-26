# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
