# Logs API

---

## Query Logs

Retrieve paginated log entries from a single file or across all log files.

```http
GET /log-viewer/api/logs
```

### Request Parameters

| Parameter | Type | Default | Description |
|---|---|---|---|
| `file` | `string` | `null` | File identifier (e.g. `a11be773-laravel.log`). If omitted, searches all files. |
| `query` | `string` | `null` | Search query or regex pattern. |
| `direction` | `string` | `'desc'` | Sort order: `'desc'` (newest first) or `'asc'` (oldest first). |
| `levels` | `array` | `[]` | Filter by severity levels: `['debug', 'info', 'warning', 'error', 'critical']`. |
| `page` | `int` | `1` | Pagination page number. |
| `per_page` | `int` | `25` | Number of entries per page. |
| `seek` | `int` | `null` | Unix timestamp to seek to. Returns the page containing this timestamp. |
| `date_from` | `int` | `null` | Unix timestamp filter lower bound. |
| `date_to` | `int` | `null` | Unix timestamp filter upper bound. |
| `host` | `string` | `null` | Host identifier when querying a remote host. |

### Example Response

```json
{
  "logs": [
    {
      "index": 159,
      "level": "error",
      "datetime": "2026-09-25 21:04:43",
      "timestamp": 1790391883,
      "message": "Call to undefined method App\\Service::process()",
      "context": [],
      "extra": {
        "environment": "production"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 7,
    "per_page": 25,
    "total": 160
  },
  "seek": 1790391883,
  "seek_page": 1,
  "earliest_timestamp": 1790386877,
  "latest_timestamp": 1790391883
}
```

---

## Velocity Histogram API

Retrieve log event velocity histogram data for the interactive timeline chart.

```http
GET /log-viewer/api/logs/velocity
```

### Request Parameters

| Parameter | Type | Default | Description |
|---|---|---|---|
| `file` | `string` | `null` | File identifier. If omitted, aggregates across all logs. |
| `query` | `string` | `null` | Search query filter. |
| `levels` | `array` | `[]` | Severity levels filter. |
| `from` | `int` | `null` | Unix timestamp start range. |
| `to` | `int` | `null` | Unix timestamp end range. |
| `buckets` | `int` | `40` | Number of histogram buckets to divide the range into. |
| `host` | `string` | `null` | Remote host identifier. |

### Example Response

```json
{
  "from": 1790388283,
  "to": 1790391883,
  "buckets": 40,
  "bucket_size": 90,
  "timeline": [
    {
      "timestamp": 1790388283,
      "count": 4,
      "rate": 0.044
    },
    {
      "timestamp": 1790391800,
      "count": 45,
      "rate": 0.500
    }
  ]
}
```
