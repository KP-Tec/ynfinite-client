<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
use Ypsolution\YnfinitePhpClient\YnfiniteClient;
use Ypsolution\YnfinitePhpClient\StaticPageCache;

$dotenv = Dotenv\Dotenv::create(__DIR__. '/../');
$dotenv->load();

if(getenv('STATIC_PAGES') !== "false" && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $cachedPage = StaticPageCache::getCachedPage();
    if ($cachedPage) {
        echo $cachedPage;
        exit;
    }
}

$app = YnfiniteClient::create('../web/templates');
$app->run();