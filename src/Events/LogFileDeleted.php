<?php

namespace WgVn\TrailLogViewer\Events;

use Illuminate\Foundation\Events\Dispatchable;
use WgVn\TrailLogViewer\LogFile;

class LogFileDeleted
{
    use Dispatchable;

    public function __construct(
        public LogFile $file
    ) {}
}
