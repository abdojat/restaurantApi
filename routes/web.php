<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;

// Handle direct storage access with CORS headers
Route::options('storage/{path}', [ImageController::class, 'handleOptions'])->where('path', '.*');
Route::get('storage/{path}', [ImageController::class, 'serveImage'])->where('path', '.*');

Route::any('{any}', function () {
    return view('welcome');
})->where('any', '^(?!api|storage)(.*)$');
