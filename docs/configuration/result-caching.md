# Result Caching & Indexing

To deliver near-instant response times across millions of log lines, Trail Log Viewer builds lightweight binary chunk indices that record byte offsets, timestamps, and severity levels.

Because log files are **append-only**, previously scanned portions are cached permanently; subsequent requests scan only newly appended bytes.

---

## Cache Driver

By default, Trail Log Viewer uses your application's default cache driver (`config('cache.default')`).

To store indices in a fast, dedicated cache store such as Redis:

```env
LOG_VIEWER_CACHE_DRIVER=redis
```

Or configure it in `config/log-viewer.php`:

```php
'cache_driver' => env('LOG_VIEWER_CACHE_DRIVER', 'redis'),
```

### Temporary / In-Memory Cache
If you do not wish to persist indices on disk or Redis, you can use the `'array'` driver:

```env
LOG_VIEWER_CACHE_DRIVER=array
```
*(Note: With the `array` driver, indices must be recomputed for every request).*

---

## Index Chunk Size

Indices are broken down into chunks (default: 500 records per chunk) to avoid loading massive arrays into memory when paginating:

```php
'chunk_size' => 500,
```

---

## Cache Compression

Cache compression is enabled automatically when the PHP `zlib` extension is available. Compressed index chunks consume 3x to 4x less cache storage.

---

## Cache Prefix

To avoid any key collisions with your application's business cache, all cache keys are prefixed:

```php
'cache_key_prefix' => 'lv',
```

---

## Clearing Indices

### Via the User Interface
- Click the options menu (`⋮`) next to any folder or file and click **Clear index**.
- Click the global dropdown in the sidebar to select **Clear all indices**.

### Via the API
Send a `DELETE` request to:
- Individual file: `DELETE /log-viewer/api/files/{fileIdentifier}/cache`
- Entire folder: `DELETE /log-viewer/api/folders/{folderIdentifier}/cache`
