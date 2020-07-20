<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Utils\Cache\StaticPageCache;

$dotenv = Dotenv\Dotenv::create(__DIR__. '/../../');
$dotenv->load();