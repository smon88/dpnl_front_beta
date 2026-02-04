<?php

if (!function_exists('versioned_asset')) {
    /**
     * Generate a versioned asset URL for cache busting.
     * Appends file modification time as query parameter.
     *
     * @param string $path
     * @return string
     */
    function versioned_asset($path) {
        $fullPath = public_path($path);
        if (file_exists($fullPath)) {
            return asset($path) . '?v=' . filemtime($fullPath);
        }
        return asset($path);
    }
}
