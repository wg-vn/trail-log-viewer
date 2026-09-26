# Files API

---

## Download Log File

Stream and download a raw log file:

```http
GET /log-viewer/api/files/{fileIdentifier}/download
```

---

## Clear File Index Cache

Clear the cached binary index for a single file, forcing it to re-index on next scan:

```http
DELETE /log-viewer/api/files/{fileIdentifier}/cache
```

---

## Delete Log File

Delete a log file from the server disk:

```http
DELETE /log-viewer/api/files/{fileIdentifier}
```
