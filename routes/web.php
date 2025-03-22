<?php

use App\Http\Middleware\RestrictAudioAccess;
use Google\Cloud\Storage\Connection\Rest;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});


