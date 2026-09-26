# Folders API

---

## List Folders & Files

Retrieve the complete log directory structure, including nested folders and file metadata.

```http
GET /log-viewer/api/folders
```

### Example Response

```json
[
  {
    "identifier": "6113b5b2",
    "name": "root",
    "is_root": true,
    "earliest_timestamp": 1790386877,
    "latest_timestamp": 1790391883,
    "files": [
      {
        "identifier": "a11be773-laravel.log",
        "name": "laravel.log",
        "size_formatted": "376.55 KB",
        "earliest_timestamp": 1790386877,
        "latest_timestamp": 1790391883,
        "can_download": true,
        "can_delete": true
      }
    ]
  }
]
```

---

## Download Folder Archive

Stream a `.zip` archive containing all log files inside a folder:

```http
GET /log-viewer/api/folders/{folderIdentifier}/download
```

---

## Clear Folder Cache

Clear index caches for all files within a folder:

```http
DELETE /log-viewer/api/folders/{folderIdentifier}/cache
```

---

## Delete Folder

Delete all log files within a folder:

```http
DELETE /log-viewer/api/folders/{folderIdentifier}
```
