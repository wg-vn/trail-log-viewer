# Hosts API

---

## List Configured Hosts

Retrieve all local and remote hosts configured in `config/log-viewer.php`:

```http
GET /log-viewer/api/hosts
```

### Example Response

```json
[
  {
    "identifier": "local",
    "name": "Local Environment",
    "is_remote": false
  },
  {
    "identifier": "production-web-1",
    "name": "Production Web #1",
    "is_remote": true
  }
]
```
