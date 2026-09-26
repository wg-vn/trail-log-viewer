<?php

namespace WgVn\TrailLogViewer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use WgVn\TrailLogViewer\Exceptions\InvalidRegularExpression;
use WgVn\TrailLogViewer\Facades\LogViewer;
use WgVn\TrailLogViewer\Http\Resources\LevelCountResource;
use WgVn\TrailLogViewer\Http\Resources\LogFileResource;
use WgVn\TrailLogViewer\Http\Resources\LogResource;
use WgVn\TrailLogViewer\Logs\Log;

class LogsController
{
    const OLDEST_FIRST = 'asc';
    const NEWEST_FIRST = 'desc';

    public function index(Request $request)
    {
        $fileIdentifier = $request->query('file', '');
        $query = $request->query('query', '');
        $direction = $request->query('direction', 'desc');
        $log = $request->query('log', null);
        $excludedLevels = $request->query('exclude_levels', []);
        $excludedFileTypes = $request->query('exclude_file_types', []);
        $perPage = $request->query('per_page', 25);
        $seek = $request->query('seek') ?? $request->query('t');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        session()->put('log-viewer:shorter-stack-traces', $request->boolean('shorter_stack_traces', false));
        $hasMoreResults = false;
        $percentScanned = 0;

        if ($request->query('page', 1) < 1) {
            $request->replace(['page' => 1]);
        }

        if ($file = LogViewer::getFile($fileIdentifier)) {
            $logQuery = $file->logs();
            $logClass = $file->type()->logClass();
        } elseif (! empty($query)) {
            $fileCollection = LogViewer::getFiles();

            if (! empty($excludedFileTypes)) {
                $fileCollection = $fileCollection->filter(function ($file) use ($excludedFileTypes) {
                    return ! in_array($file->type()->value, $excludedFileTypes);
                })->values();
            }

            $logQuery = $fileCollection->logs();
            $logClass = Log::class;
        }

        $seekPage = null;

        if (isset($logQuery)) {
            try {
                $logQuery->search($query);

                if (isset($file) && Str::startsWith($query, 'log-index:')) {
                    $logIndex = explode(':', $query)[1];
                    $expandAutomatically = intval($logIndex) || $logIndex === '0';
                }

                if ($direction === self::NEWEST_FIRST) {
                    $logQuery->reverse();
                }

                $logQuery->scan();
                $logQuery->exceptLevels($excludedLevels);

                if (! empty($dateFrom) || ! empty($dateTo)) {
                    $logQuery->forDateRange($dateFrom ? (int) $dateFrom : null, $dateTo ? (int) $dateTo : null);
                }

                if (! empty($seek) && ! $request->has('page')) {
                    if (method_exists($logQuery, 'findPageForTimestamp')) {
                        $seekPage = $logQuery->findPageForTimestamp((int) $seek, (int) $perPage, $direction);
                        $request->replace(['page' => $seekPage]);
                    }
                }

                $logs = $logQuery->paginate((int) $perPage);
                $levels = array_values($logQuery->getLevelCounts());

                if ($logs->lastPage() < $request->input('page', 1)) {
                    $request->replace(['page' => $logs->lastPage() ?? 1]);
                    // re-create the paginator instance to fix a bug
                    $logs = $logQuery->paginate($perPage);
                }

                $hasMoreResults = $logQuery->requiresScan();
                $percentScanned = $logQuery->percentScanned();
            } catch (InvalidRegularExpression $exception) {
                $queryError = $exception->getMessage();
            }
        }

        return response()->json([
            'file' => isset($file) ? new LogFileResource($file) : null,
            'levelCounts' => LevelCountResource::collection($levels ?? []),
            'logs' => LogResource::collection($logs ?? []),
            'columns' => isset($logClass) ? ($logClass::$columns ?? null) : null,
            'pagination' => isset($logs) ? [
                'current_page' => $logs->currentPage(),
                'first_page_url' => $logs->url(1),
                'from' => $logs->firstItem(),
                'last_page' => $logs->lastPage(),
                'last_page_url' => $logs->url($logs->lastPage()),
                'links' => $logs->linkCollection()->toArray(),
                'links_short' => $logs->onEachSide(0)->linkCollection()->toArray(),
                'next_page_url' => $logs->nextPageUrl(),
                'path' => $logs->path(),
                'per_page' => $logs->perPage(),
                'prev_page_url' => $logs->previousPageUrl(),
                'to' => $logs->lastItem(),
                'total' => $logs->total(),
            ] : null,
            'seek' => $seek ? (int) $seek : null,
            'seek_page' => $seekPage,
            'earliest_timestamp' => isset($file) ? $file->getMetadata('earliest_timestamp') : null,
            'latest_timestamp' => isset($file) ? $file->getMetadata('latest_timestamp') : null,
            'expandAutomatically' => $expandAutomatically ?? false,
            'cacheRecentlyCleared' => $this->cacheRecentlyCleared ?? false,
            'hasMoreResults' => $hasMoreResults,
            'percentScanned' => $percentScanned,
            'performance' => $this->getRequestPerformanceInfo(),
        ]);
    }

    public function velocity(Request $request)
    {
        $fileIdentifier = $request->query('file', '');
        $query = $request->query('query', '');
        $range = $request->query('range', '1h');
        $from = $request->query('from');
        $to = $request->query('to');
        $excludedLevels = $request->query('exclude_levels', []);
        $excludedFileTypes = $request->query('exclude_file_types', []);

        if ($file = LogViewer::getFile($fileIdentifier)) {
            $logQuery = $file->logs();
        } else {
            $fileCollection = LogViewer::getFiles();
            if (! empty($excludedFileTypes)) {
                $fileCollection = $fileCollection->filter(function ($file) use ($excludedFileTypes) {
                    return ! in_array($file->type()->value, $excludedFileTypes);
                })->values();
            }
            $logQuery = $fileCollection->logs();
        }

        $logQuery->search($query);
        $logQuery->scan();
        $logQuery->exceptLevels($excludedLevels);

        $now = time();
        if (empty($from)) {
            $seconds = match ($range) {
                '15m' => 15 * 60,
                '1h' => 60 * 60,
                '6h' => 6 * 3600,
                '12h' => 12 * 3600,
                '24h' => 24 * 3600,
                '7d' => 7 * 86400,
                'all' => null,
                default => 3600,
            };

            if ($seconds) {
                $from = $now - $seconds;
                $to = $now;
            }
        }

        $data = $logQuery->getVelocity(
            bucketCount: 40,
            from: $from ? (int) $from : null,
            to: $to ? (int) $to : null,
        );

        return response()->json($data);
    }

    protected function getRequestPerformanceInfo(): array
    {
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : request()->server('REQUEST_TIME_FLOAT');
        $memoryUsage = number_format(memory_get_peak_usage(true) / 1024 / 1024, 2).' MB';
        $requestTime = number_format((microtime(true) - $startTime) * 1000, 0).'ms';

        return [
            'memoryUsage' => $memoryUsage,
            'requestTime' => $requestTime,
            'version' => LogViewer::version(),
        ];
    }
}
