# Papertrail Observability Features Overview

Trail Log Viewer reproduces the intuitive, high-velocity developer experience of **Papertrail** and **SolarWinds Observability Logs**.

All primary inspection workflows are anchored by the **Docked Bottom Toolbar**, ensuring search controls, timeline seeking, velocity histograms, display options, and live tailing are always within reach without scrolling away from your logs.

---

## Docked Bottom Toolbar

![Toolbar](../img/toolbar.png)

The bottom toolbar contains the following controls from left to right:

1. **Search Input Bar:** Full-width search bar with support for phrases, boolean expressions, negation, and regex.
2. **Recent Searches (`≡`):** Dropdown showing up to 25 recent queries with relative timestamps.
3. **Save This Search (`🔖`):** One-click bookmark modal to store named searches.
4. **Search Tips (`?`):** Syntax cheat-sheet modal with interactive **Use** presets.
5. **SEARCH Button:** Blue execution button that submits the query and initiates index scanning.
6. **Seek to Date or Time (`🕒`):** Popover providing instant jump presets (`5m ago`, `1h ago`, etc.) and a datetime picker.
7. **Toggle Velocity Graph (`📊`):** Expandable SVG event frequency histogram with Rate and Count modes.
8. **Display Preferences (`⚙️`):** Customizable typography, comfort/compact density, highlights, UTC, and column pills.
9. **Create Alert for Search (`⚠️`):** Configure alert triggers, thresholds, and notification channels.
10. **Reload Logs (`🔄`):** Refresh the current log file and rebuild indices.
11. **Live Tail Stream (`▶` / `⏸`):** Toggle real-time log polling with auto-scroll and pause-on-scroll detection.

---

## Feature Guides

- [Recent & Saved Searches](./searches-and-tips.md)
- [Seek to Date or Time](./seek.md)
- [Interactive Velocity Graph](./velocity-graph.md)
- [Display Preferences](./display-preferences.md)
- [Alerts & Live Tail Streaming](./alerts-and-live-tail.md)
