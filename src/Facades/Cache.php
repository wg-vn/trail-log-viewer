<?php

namespace WgVn\TrailLogViewer\Facades;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Illuminate\Contracts\Cache\Repository
 *
 * @see Repository
 */
class Cache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'log-viewer-cache';
    }
}
