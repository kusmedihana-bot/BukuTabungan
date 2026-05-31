<?php
/**
 * Auto-detect base URL — works on localhost/subfolder AND Railway root.
 * Include this once; use BASE_URL everywhere instead of hardcoded paths.
 */
if (!defined('BASE_URL')) {
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Walk up from the current script to find the app root (where index.php lives)
    $docRoot  = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
    $appRoot  = dirname(__FILE__, 2); // two levels up from config/
    $subPath  = str_replace($docRoot, '', $appRoot);
    $subPath  = str_replace('\\', '/', $subPath); // Windows compat
    define('BASE_URL', rtrim($scheme . '://' . $host . $subPath, '/'));
}
