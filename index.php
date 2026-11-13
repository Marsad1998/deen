<?php

/**
 * DEEN Electronics - Shared Hosting Front Controller Bridge
 *
 * For shared hosting platforms (such as Hostinger public_html) where the webroot
 * points directly to the project root directory rather than /public.
 * This forwards execution seamlessly to public/index.php while keeping local
 * `php artisan serve` and Apache/LiteSpeed web environments 100% compatible.
 */

$publicIndex = __DIR__ . '/public/index.php';

if (file_exists($publicIndex)) {
    require_once $publicIndex;
} else {
    http_response_code(500);
    echo 'DEEN Electronics: Application front controller (public/index.php) not found.';
}
