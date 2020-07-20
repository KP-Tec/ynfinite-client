<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Utils\Cache\StaticPageCache;

$dotenv = Dotenv\Dotenv::create(__DIR__. '/../');
$dotenv->load();

if(getenv('STATIC_PAGES') !== "false" && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $cachedPage = StaticPageCache::getCachedPage();
    if ($cachedPage) {
        echo $cachedPage;
        exit;
    }
}

(require __DIR__ . '/../config/bootstrap.php')->run();