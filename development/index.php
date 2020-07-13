<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
use Ypsolution\YnfinitePhpClient\YnfiniteClient;


$app = YnfiniteClient::create('templates');
$app->run();