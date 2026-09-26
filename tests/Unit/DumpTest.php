<?php

test('dumps', function () {
    expect('dd')->not->toBeUsed()
        ->and('dump')->toOnlyBeUsedIn('WgVn\TrailLogViewer\Utils\Benchmark');
});
