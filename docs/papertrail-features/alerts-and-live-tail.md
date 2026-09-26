# Alerts & Live Tail Streaming

## Live Tail Streaming

Live Tail streams newly written logs into your browser in real-time.

### Starting and Stopping Live Tail
- Click the **Play** button (`▶`) on the far right of the bottom toolbar to begin live tailing.
- The button turns green with a **Pause** icon (`⏸`), and an emerald pulsating `• Live Tail` badge appears in the top navigation bar.
- Polling runs automatically every 2.5 seconds.
- New logs are appended and the view auto-scrolls smoothly to keep the latest entries visible.

### Intelligent Scroll-Away Detection
If you scroll away to inspect older logs:
1. Live Tail automatically pauses so the page doesn't jump while you are reading.
2. The top badge turns amber: `• Tail Paused Resume`.
3. A floating emerald button appears: **"Tail paused (scrolled) · Click to jump to newest logs"**.
4. Clicking the floating banner or **Resume** immediately scrolls to the newest entries and resumes streaming.

---

## Search Query Alerts

Set up automated alerts for critical queries (e.g. `OutOfMemoryError` or `PaymentFailed`).

### Creating an Alert
1. Click the alert warning icon (`⚠️`) in the bottom toolbar.
2. Under the **Create Alert** tab:
   - **Alert Name:** e.g., `Critical Payment Errors`.
   - **Search Query Filter:** e.g., `level:critical OR "PaymentException"`.
   - **Severity:** `Critical`, `Warning`, or `Info`.
   - **Trigger Condition:** Specify event count and time window (e.g., `> 1 events occur within 1 min`).
   - **Notification Action:**
     - `Browser Alert`: Displays floating in-app toasts and native HTML5 desktop push notifications.
     - `Webhook`: Post JSON payloads to Slack, Discord, or your endpoint.
     - `Email`: Send notifications to ops emails.
3. Click **Create Alert**.

### Testing & Managing Alerts
Switch to the **Manage Alerts** tab to:
- Toggle alerts **ON** or **OFF**.
- Click **Test** to simulate an immediate alert trigger and preview the toast notification.
- Delete obsolete alerts.
