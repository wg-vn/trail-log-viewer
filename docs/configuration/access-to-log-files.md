# Authorizing File Actions (Download & Delete)

Trail Log Viewer provides fine-grained authorization gates to control who can download or delete individual log files and log folders.

By default, if the user has access to view Trail Log Viewer, downloading and deleting are permitted. You can restrict these actions using dedicated Laravel Gates.

---

## Available Authorization Gates

| Gate Name | Arguments | Description |
|---|---|---|
| `downloadLogFile` | `(?User $user, LogFile $file)` | Checked before streaming a `.log` file download. |
| `downloadLogFolder` | `(?User $user, LogFolder $folder)` | Checked before generating a `.zip` archive of a folder. |
| `deleteLogFile` | `(?User $user, LogFile $file)` | Checked before deleting a `.log` file from disk. |
| `deleteLogFolder` | `(?User $user, LogFolder $folder)` | Checked before deleting all files in a folder. |

---

## Defining File Gates

Define any of the gates in your `AuthServiceProvider`:

```php
namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use WgVn\TrailLogViewer\LogFile;
use WgVn\TrailLogViewer\LogFolder;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPolicies();

        // Prevent deleting production logs
        Gate::define('deleteLogFile', function (?User $user, LogFile $file) {
            if ($file->name === 'laravel.log') {
                return false;
            }
            return $user && $user->is_super_admin;
        });

        // Restrict folder downloads to engineers
        Gate::define('downloadLogFolder', function (?User $user, LogFolder $folder) {
            return $user && $user->role === 'engineer';
        });
    }
}
```

When a gate returns `false`, the corresponding download or delete button is disabled in the UI and the API returns a `403 Forbidden` response.
