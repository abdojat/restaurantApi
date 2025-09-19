<?php

use Illuminate\Support\Facades\Route;

// Handle direct storage access with CORS headers
Route::get('storage/{path}', function ($path) {
    try {
        // Security: Prevent directory traversal
        $path = str_replace(['../', '..\\'], '', $path);
        
        $fullPath = storage_path('app/public/' . $path);
        
        if (!file_exists($fullPath)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        $file = file_get_contents($fullPath);
        $mimeType = mime_content_type($fullPath) ?: 'image/jpeg';
        
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
            
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to serve file'], 500);
    }
})->where('path', '.*');

Route::any('{any}', function () {
    return view('welcome');
})->where('any', '^(?!api|storage)(.*)$');
