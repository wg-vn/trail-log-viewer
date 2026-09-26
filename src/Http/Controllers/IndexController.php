<?php

namespace WgVn\TrailLogViewer\Http\Controllers;

use WgVn\TrailLogViewer\Enums\SortingMethod;
use WgVn\TrailLogViewer\Facades\LogViewer;
use WgVn\TrailLogViewer\LogFolder;
use WgVn\TrailLogViewer\Utils\Utils;

class IndexController
{
    public function __invoke()
    {
        if (config('log-viewer.api_only')) {
            abort(404);
        }

        $files_sort_by_time = config('log-viewer.defaults.file_sorting_method') === SortingMethod::ModifiedTime;
        $assetsPublished = LogViewer::assetsArePublished();

        return view(LogViewer::getViewLayout(), [
            'assetsPublished' => $assetsPublished,
            'logViewerScriptVariables' => [
                'headers' => (object) [],
                'assets_outdated' => $assetsPublished && ! LogViewer::assetsAreCurrent(),
                'version' => LogViewer::version(),
                'app_name' => config('app.name'),
                'path' => config('log-viewer.route_path'),
                'back_to_system_url' => config('log-viewer.back_to_system_url'),
                'back_to_system_label' => config('log-viewer.back_to_system_label'),
                'files_sort_by_time' => $files_sort_by_time,
                'max_log_size_formatted' => Utils::bytesForHumans(LogViewer::maxLogSize()),

                'supports_hosts' => LogViewer::supportsHostsFeature(),
                'hosts' => LogViewer::getHosts(),
                'per_page_options' => config('log-viewer.per_page_options') ?? [10, 25, 50, 100, 250, 500],
                'defaults' => [
                    'use_local_storage' => config('log-viewer.defaults.use_local_storage'),
                    'log_sorting_order' => config('log-viewer.defaults.log_sorting_order'),
                    'per_page' => config('log-viewer.defaults.per_page'),
                    'theme' => config('log-viewer.defaults.theme'),
                    'shorter_stack_traces' => config('log-viewer.defaults.shorter_stack_traces'),
                ],
                'root_folder_prefix' => LogFolder::rootPrefix(),
            ],
        ]);
    }
}
