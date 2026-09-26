# Interactive Velocity Graph

The Velocity Graph provides an event frequency histogram rendered directly above the bottom toolbar, matching the Papertrail timeline.

---

## Toggling the Graph

Click the graph icon (`📊`) in the bottom toolbar to expand or collapse the Velocity Graph.

---

## Features

### 1. Rate vs. Count Modes
- **Count Mode:** Shows the total number of log events that occurred in each time bucket.
- **Rate Mode:** Shows the event frequency normalized to events per second (`events/sec`).

### 2. Time Window Selector
Zoom the timeline to different intervals via the dropdown:
- `10 minutes`
- `30 minutes`
- `1 hour`
- `6 hours`
- `24 hours`
- `All time`

### 3. Interactive Tooltips & Click-to-Seek
Hover over any bar in the histogram to inspect:
- Number of log events (or event rate).
- Exact bucket time interval (start time to end time).

Clicking any bar instantly triggers a **Seek** to that bucket's timestamp, updating the log table immediately.

### 4. Auto-Refresh Sync
When enabled, the velocity chart automatically polls the `/log-viewer/api/logs/velocity` endpoint to visualize incoming log spikes in real-time.
