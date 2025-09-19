<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Serve images with proper CORS headers
     */
    public function serveImage($path)
    {
        try {
            // Security: Prevent directory traversal
            $path = str_replace(['../', '..\\'], '', $path);
            
            $fullPath = storage_path('app/public/' . $path);
            
            // Log for debugging
            Log::info('Image request:', [
                'requested_path' => $path,
                'full_path' => $fullPath,
                'exists' => file_exists($fullPath)
            ]);
            
            if (!file_exists($fullPath)) {
                Log::warning('Image not found:', ['path' => $fullPath]);
                return response()->json([
                    'error' => 'Image not found',
                    'path' => $path,
                    'debug' => 'File does not exist: ' . $fullPath
                ], 404)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            }
            
            $file = file_get_contents($fullPath);
            $mimeType = mime_content_type($fullPath) ?: 'image/jpeg';
            
            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Cache-Control', 'public, max-age=3600')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
                
        } catch (\Exception $e) {
            Log::error('Image route error:', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Failed to serve image',
                'message' => $e->getMessage()
            ], 500)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }
    }

    /**
     * Handle CORS preflight requests
     */
    public function handleOptions()
    {
        return response('', 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->header('Access-Control-Max-Age', '86400');
    }
}
