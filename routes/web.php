<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'API Inspeccion',
        'status' => 'running',
        'version' => 'v1',
    ]);
});
