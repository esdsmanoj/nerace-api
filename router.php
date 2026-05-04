<?php
// Router for PHP built-in server (replaces mod_rewrite)

// Polyfill for getallheaders() — not available in PHP built-in server
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', ucwords(strtolower(substr($key, 5)), '_'));
                $headers[$header] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
                $headers[str_replace('_', '-', ucwords(strtolower($key), '_'))] = $value;
            }
        }
        return $headers;
    }
}

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Serve static file directly
}

$_SERVER['PATH_INFO'] = $uri;
require_once __DIR__ . '/index.php';
