<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;

use function Orchestra\Testbench\package_path;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Besides the generated Laravel log, show the Apache and Nginx samples used by the test suite.
        config(['log-viewer.include_files' => [
            '*.log',
            '**/*.log',
            package_path('tests/Unit/AccessLogs/Fixtures/*.log') => 'HTTP samples',
        ]]);
    }
}
