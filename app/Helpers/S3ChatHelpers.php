<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

if (!function_exists('uploadS3File')) {
    /**
     * Upload file to LOCAL storage (public disk).
     * Keeping function name to avoid refactoring call sites.
     */
    function uploadS3File($directory, $file)
    {
        // $directory is the folder name, e.g., 'chats'
        // putFile on 'public' disk stores in storage/app/public/{directory}/{hash}.{ext}
        return Storage::disk('public')->putFile($directory, $file);
    }
}

if (!function_exists('getS3Url')) {
    /**
     * Get URL for LOCAL storage file.
     * Keeping function name to avoid refactoring call sites.
     */
    function getS3Url($directory, $path, $type = null)
    {
        // Storage::disk('public')->url($path) returns /storage/{path}
        // This requires 'php artisan storage:link' to be run.
        if (!$path)
            return null;
        return Storage::disk('public')->url($path);
    }
}
