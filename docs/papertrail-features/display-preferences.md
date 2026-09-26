# Display Preferences

Click the gear icon (`⚙️`) in the docked bottom toolbar to open the **Display Preferences** popover. All settings are persisted in your browser's `localStorage` and apply instantly without page reloads.

---

## Typography & Sizing

- **Font Family:**
  - `System Monospace`
  - `Fira Code`
  - `JetBrains Mono`
  - `Source Code Pro`
  - `Courier New`
- **Font Size:**
  - `Compact (12px)`
  - `Small (13px)`
  - `Default (14px)`
  - `Large (16px)`

---

## Row Density

- **Comfort:** Generous vertical padding (`py-1.5`) for readable inspection.
- **Compact:** High-density condensed rows (`py-0.5`) allowing more logs to fit on screen simultaneously.

---

## Toggles

- **Highlight Matches:** Highlights search keywords inside log messages with high-contrast amber badges.
- **Truncate Message (Single Line):** Keeps rows strictly single-line with ellipsis truncation, preserving table alignment. Turn off to wrap long messages and stack traces.
- **UTC Timestamps:** Converts displayed timestamps from local/app timezone to UTC.
- **Reverse Tail:** Flips display order between newest-first (descending) and oldest-first (chronological).

---

## Column Visibility Pills

Toggle visibility for individual log table columns:
- `Datetime`
- `Severity`
- `Env`
