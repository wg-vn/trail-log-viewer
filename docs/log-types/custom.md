# Custom Log Types

You can register custom log parsers to parse proprietary log formats, third-party service logs, or custom JSON structures.

---

## Creating a Custom Log Class

Create a class extending `WgVn\TrailLogViewer\Logs\BaseLog`:

```php
namespace App\Logs;

use WgVn\TrailLogViewer\Logs\BaseLog;

class CustomAppLog extends BaseLog
{
    public static string $name = 'Custom App Log';
    public static string $type = 'custom_app';

    // Regex pattern to match log entry header:
    public static string $regex = '/^\[(?P<datetime>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (?P<level>\w+): (?P<message>.*)/';

    protected function fillMatches(array $matches = []): void
    {
        $this->datetime = $matches['datetime'] ?? null;
        $this->level = strtolower($matches['level'] ?? 'info');
        $this->message = $matches['message'] ?? '';
    }
}
```

---

## Registering Custom Log Types

Register your custom log class in `config/log-viewer.php`:

```php
'patterns' => [
    \App\Logs\CustomAppLog::class,
],
```

Or dynamically via the facade in a service provider:

```php
use WgVn\TrailLogViewer\Facades\LogViewer;
use App\Logs\CustomAppLog;

public function boot(): void
{
    LogViewer::registerLogType(CustomAppLog::class);
}
```
