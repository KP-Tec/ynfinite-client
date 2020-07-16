<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
use Ypsolution\YnfinitePhpClient\YnfiniteClient;
use Ypsolution\YnfinitePhpClient\StaticPageCache;

$dotenv = Dotenv\Dotenv::create(getcwd());
$dotenv->load();

var_dump(getenv('STATIC_PAGES'));

if(getenv('STATIC_PAGES') && $_SERVER['REQUEST_METHOD'] === 'GET') {
    var_dump("GOT CHACHED");
    $cachedPage = StaticPageCache::getCachedPage();
    if ($cachedPage) {
        echo $cachedPage;
        exit;
    }
}

$app = YnfiniteClient::create('web/templates');
$app->run();