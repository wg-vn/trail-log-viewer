# Seek to Date or Time

Trail Log Viewer allows you to jump directly to any point in time within your logs.

---

## Opening the Seek Popover

Click the clock icon (`🕒`) in the docked bottom toolbar to open the Seek popover.

---

## Quick Jump Presets

The popover provides 1-click jump buttons:

- **5m ago:** Jumps to logs recorded 5 minutes before current time.
- **15m ago:** Jumps to 15 minutes ago.
- **1h ago:** Jumps to 1 hour ago.
- **6h ago:** Jumps to 6 hours ago.
- **24h ago:** Jumps to 24 hours ago.
- **Now:** Jumps to the most recent logs.

---

## Custom Timestamps & Date Picker

- **Text Input:** Type human timestamps such as `8:11am`, `Tue 4pm`, `Dec 1 15:30`, or standard ISO strings (`2026-09-25 21:00:00`).
- **Date Picker:** Click the calendar icon to select a date and time with the browser's native datetime-local picker.
- Click **Seek to** to execute the jump.

---

## How It Works Under the Hood

When a seek is triggered:
1. The timestamp is converted to a Unix epoch and passed to the backend API (`GET /log-viewer/api/logs?seek=1790388429`).
2. The `IndexedLogReader` calculates which cached index page contains the closest entry using `findPageForTimestamp()`.
3. The response returns `seek_page`, and the frontend automatically navigates to that page and highlights the position.
4. The timestamp is preserved in the URL query string (`?seek=...`) so you can copy and share the exact time link with teammates.
