<?php
/**
 * Reliable BASE_URL detection for Railway, shared hosting, and localhost.
 * Strategy: derive the URL from the REQUEST_URI, not the filesystem.
 */
if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
              || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
              ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Find how deep we are from the web root by looking at SCRIPT_NAME
    // e.g. /bukutabungan/index.php  → base = /bukutabungan
    //      /index.php               → base = ''
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $base = rtrim(dirname($scriptName), '/');

    define('BASE_URL', $scheme . '://' . $host . $base);
}
<?php // cache bust Sun May 31 11:53:53 UTC 2026 ?>
