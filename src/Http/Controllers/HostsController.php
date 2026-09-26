<?php

namespace WgVn\TrailLogViewer\Http\Controllers;

use WgVn\TrailLogViewer\Facades\LogViewer;
use WgVn\TrailLogViewer\Http\Resources\LogViewerHostResource;

class HostsController
{
    public function index()
    {
        return LogViewerHostResource::collection(
            LogViewer::getHosts()
        );
    }
}
