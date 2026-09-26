# Authorizing Access to Trail Log Viewer

By default, Trail Log Viewer is only accessible in non-production environments (when `APP_ENV` is `local`, `testing`, etc.). When `APP_ENV=production`, access is blocked until explicit authorization is defined.

---

## 1. Authorization Callback (`LogViewer::auth`)

The most flexible method is registering an authorization callback via `LogViewer::auth()` in your `AppServiceProvider` or `AuthServiceProvider`:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use WgVn\TrailLogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        LogViewer::auth(function ($request) {
            // Return true to allow access, false to deny:
            return $request->user() && in_array($request->user()->email, [
                'admin@example.com',
                'devops@example.com',
            ]);
        });
    }
}
```

---

## 2. Using Laravel Gates (`viewLogViewer`)

Trail Log Viewer natively checks for a `viewLogViewer` authorization gate. You can define this gate in your `AuthServiceProvider`:

```php
namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('viewLogViewer', function (?User $user) {
            return $user && $user->can('view-logs');
        });
    }
}
```

---

## 3. Disabling Trail Log Viewer Completely

To disable Trail Log Viewer entirely without removing the package from `composer.json`, set `LOG_VIEWER_ENABLED=false` in your `.env`:

```env
LOG_VIEWER_ENABLED=false
```

---

## Passing Custom Authorization Headers to the UI

If you use token-based authentication (such as Laravel Sanctum or JWT) and need the Vue front-end to attach an `Authorization` header to every API request:

1. Publish the view template:
   ```bash
   php artisan vendor:publish --tag="log-viewer-views"
   ```
2. Open `resources/views/vendor/log-viewer/index.blade.php`.
3. Add your authorization token to `window.LogViewer.headers`:

```html
<script>
    window.LogViewer = @json($logViewerScriptVariables);
    window.LogViewer.headers['Authorization'] = 'Bearer ' + localStorage.getItem('api_token');
</script>
```
