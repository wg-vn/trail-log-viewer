<?php

namespace WgVn\TrailLogViewer\Utils;

use WgVn\TrailLogViewer\Facades\LogViewer;
use WgVn\TrailLogViewer\LogFile;
use WgVn\TrailLogViewer\LogIndex;

class GenerateCacheKey
{
    public static function for(mixed $object, ?string $namespace = null): string
    {
        $key = '';

        if ($object instanceof LogFile) {
            $key = self::baseKey().':file:'.$object->identifier;
        }

        if ($object instanceof LogIndex) {
            $key = self::for($object->file).':'.$object->identifier;
        }

        if (is_string($object)) {
            $key = self::baseKey().':'.$object;
        }

        if (! empty($namespace)) {
            $key .= ':'.$namespace;
        }

        return $key;
    }

    protected static function baseKey(): string
    {
        return config('log-viewer.cache_key_prefix', 'lv').':'.LogViewer::version();
    }
}
