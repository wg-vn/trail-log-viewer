<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

Artisan::command('demo:prepare', function () {
    // The test suite publishes assets into the skeleton. Remove them so the demo serves the current build inline.
    File::deleteDirectory(public_path(config('log-viewer.assets_path')));

    File::delete(storage_path('logs/laravel.log'));
    $this->call('log-viewer:generate-dummy-logs', ['amount' => 200]);
})->purpose('Reset the demo app with fresh sample logs');
