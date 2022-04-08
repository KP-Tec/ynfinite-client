<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Utils\Cache\StaticCache;

$dotenv = Dotenv\Dotenv::create(__DIR__. '/../');
$dotenv->load();

if(getenv('STATIC_PAGES') !== "false" && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $cachedPage = StaticCache::getCache("PAGE");
    if ($cachedPage) {
        echo $cachedPage;
        exit;
    }
}

if(getenv('STATIC_REQUESTS') !== "false" && $_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER["REQUEST_URI"] === "/yn-form/send" && $_POST["method"] !== "post") {
    $cachedData = StaticCache::getCache("REQUEST");
    if ($cachedData) {
        header('Content-type: application/json');
        echo $cachedData;
        exit;
    }
}

(require __DIR__ . '/../config/bootstrap.php')->run();