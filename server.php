<?php

/**
 * Laravel - Built-in Development Server Router
 *
 * Emulates Apache mod_rewrite functionality for PHP's built-in web server.
 * Allows running `php artisan serve` or `php -S 127.0.0.1:8000 server.php` locally.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// If the requested URI exists in the public directory, serve it as a static asset:
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

require_once __DIR__ . '/public/index.php';
