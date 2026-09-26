<?php

use Illuminate\Support\Carbon;
use WgVn\TrailLogViewer\Readers\IndexedLogReader;

use function Pest\Laravel\getJson;

it('can load the logs for a specific file', function () {
    $logEntries = [
        makeLaravelLogEntry(),
        makeLaravelLogEntry(),
        makeLaravelLogEntry(),
    ];
    $file = generateLogFile('logcontrollertest.log', implode(PHP_EOL, $logEntries));

    $response = getJson(route('log-viewer.logs', ['file' => $file->identifier]));

    expect($response->json('logs'))->toHaveCount(count($logEntries));
});

test('simple characters can be searched case-insensitive', function () {
    $logEntries = [
        makeLaravelLogEntry(message: 'error'),
        makeLaravelLogEntry(message: 'Error'),
        makeLaravelLogEntry(message: 'eRrOr'),
        makeLaravelLogEntry(message: 'ERROR'),
        makeLaravelLogEntry(message: 'simple text'),
    ];
    $file = generateLogFile('logsearchtest.log', implode(PHP_EOL, $logEntries));

    // first, just to be sure that we're getting all the logs without any query
    $response = getJson(route('log-viewer.logs', ['file' => $file->identifier]));
    expect($response->json('logs'))->toHaveCount(count($logEntries));

    // now, with the query. Re-instantiate the log reader to make sure we don't have anything cached.
    IndexedLogReader::clearInstance($file);
    $response = getJson(route('log-viewer.logs', [
        'file' => $file->identifier,
        'query' => 'error',
    ]));
    expect($response->json('logs'))->toHaveCount(4);

});

test('unicode characters can be searched case-insensitive', function () {
    $logEntries = [
        makeLaravelLogEntry(message: 'ошибка'),
        makeLaravelLogEntry(message: 'Ошибка'),
        makeLaravelLogEntry(message: 'ошибкА'),
        makeLaravelLogEntry(message: 'ОШИБКА'),
        makeLaravelLogEntry(message: 'simple text'),
    ];
    $file = generateLogFile('logunicodetest.log', implode(PHP_EOL, $logEntries));

    // first, just to be sure that we're getting all the logs without any query
    $response = getJson(route('log-viewer.logs', ['file' => $file->identifier]));
    expect($response->json('logs'))->toHaveCount(count($logEntries));

    // now, with the query. Re-instantiate the log reader to make sure we don't have anything cached.
    IndexedLogReader::clearInstance($file);
    $response = getJson(route('log-viewer.logs', [
        'file' => $file->identifier,
        'query' => 'ошибка',
    ]));
    expect($response->json('logs'))->toHaveCount(4);
});

test('logs include full_text property by default', function () {
    $logEntries = [
        makeLaravelLogEntry(message: 'Test message'),
    ];
    $file = generateLogFile('log_with_full_text.log', implode(PHP_EOL, $logEntries));

    $response = getJson(route('log-viewer.logs', ['file' => $file->identifier]));

    expect($response->json('logs'))->toHaveCount(1);
    expect($response->json('logs.0'))->toHaveKey('full_text');
});

test('logs can exclude full_text property when requested', function () {
    $logEntries = [
        makeLaravelLogEntry(message: 'Test message'),
    ];
    $file = generateLogFile('log_without_full_text.log', implode(PHP_EOL, $logEntries));

    $response = getJson(route('log-viewer.logs', [
        'file' => $file->identifier,
        'exclude_full_text' => true,
    ]));

    expect($response->json('logs'))->toHaveCount(1);
    expect($response->json('logs.0'))->not->toHaveKey('full_text');
});

test('velocity endpoint returns histogram buckets', function () {
    $logEntries = [
        makeLaravelLogEntry(date: Carbon::parse('2026-09-26 01:00:00'), message: 'First entry'),
        makeLaravelLogEntry(date: Carbon::parse('2026-09-26 01:10:00'), message: 'Second entry'),
        makeLaravelLogEntry(date: Carbon::parse('2026-09-26 01:20:00'), message: 'Third entry'),
    ];
    $file = generateLogFile('log_velocity_test.log', implode(PHP_EOL, $logEntries));

    $response = getJson(route('log-viewer.logs.velocity', [
        'file' => $file->identifier,
        'range' => 'all',
    ]));

    $response->assertOk();
    expect($response->json('buckets'))->toBeArray();
    expect($response->json('total'))->toBe(3);
    expect($response->json('earliest_timestamp'))->toBeGreaterThan(0);
    expect($response->json('latest_timestamp'))->toBeGreaterThan(0);
});

test('seek parameter jumps to the correct page for timestamp', function () {
    $entries = [];
    $baseTime = strtotime('2026-09-26 00:00:00');
    for ($i = 0; $i < 60; $i++) {
        $date = Carbon::createFromTimestamp($baseTime + ($i * 60));
        $entries[] = makeLaravelLogEntry(date: $date, message: "Entry $i");
    }
    $file = generateLogFile('log_seek_test.log', implode(PHP_EOL, $entries));

    $targetTimestamp = strtotime('2026-09-26 00:15:00');

    $response = getJson(route('log-viewer.logs', [
        'file' => $file->identifier,
        'seek' => $targetTimestamp,
        'per_page' => 25,
        'direction' => 'desc',
    ]));

    $response->assertOk();
    expect($response->json('pagination.current_page'))->toBe(2);
    expect($response->json('seek'))->toBe($targetTimestamp);
});

test('date_from and date_to parameters filter logs by timestamp range', function () {
    $logEntries = [
        makeLaravelLogEntry(date: Carbon::parse('2026-09-26 01:00:00'), message: 'Old entry'),
        makeLaravelLogEntry(date: Carbon::parse('2026-09-26 02:00:00'), message: 'Target entry 1'),
        makeLaravelLogEntry(date: Carbon::parse('2026-09-26 02:30:00'), message: 'Target entry 2'),
        makeLaravelLogEntry(date: Carbon::parse('2026-09-26 04:00:00'), message: 'Future entry'),
    ];
    $file = generateLogFile('log_daterange_test.log', implode(PHP_EOL, $logEntries));

    $from = strtotime('2026-09-26 01:30:00');
    $to = strtotime('2026-09-26 03:00:00');

    $response = getJson(route('log-viewer.logs', [
        'file' => $file->identifier,
        'date_from' => $from,
        'date_to' => $to,
    ]));

    $response->assertOk();
    expect($response->json('logs'))->toHaveCount(2);
});
