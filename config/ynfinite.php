<?php

$dotenv = Dotenv\Dotenv::create(__DIR__ . "/../");
$dotenv->load();

$ynfinite_server = 'https://live-server.ynfinite.de';
$ynfinite_port = 443;
$fileservice_server = 'https://live-files.ynfinite.de';
$fileservice_port = 443;
if (getenv('DEV') !== "false") {
    $ynfinite_server = 'https://ynfinite-node';
    $ynfinite_port = 4242;
    $fileservice_server = 'http://imageservice';
    $fileservice_port = 3333;
}


function toBoolean($value) {
    if(!$value || $value === "false") return false;
    return true;
}

return [
    "debugTemplates" => toBoolean(getenv("DEBUG_TEMPLATES")),
    'installPassword' => getenv('YN_INSTALL_PASSWORD'),
    'pageTypes' => array("html", "htm"),
    "db" => array(
        'driver' => 'mysql',
        'host' => getenv("DB_HOST"),
        'username' => getenv("DB_USER"),
        'database' => getenv("DB_NAME"),
        'password' => getenv("DB_PASSWORD"),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'options' => [
            // Turn off persistent connections
            PDO::ATTR_PERSISTENT => false,
            // Enable exceptions
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Emulate prepared statements
            PDO::ATTR_EMULATE_PREPARES => true,
            // Set default fetch mode to array
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Set character set
            // PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
        ],        
    ),
    'services' => [
        'frontend' => [
            'host' => $ynfinite_server,
            'port' => $ynfinite_port,
            'controller' => "/v1/cms/newfrontend/p/",
            'gdprInfo' => "/v1/cms/newfrontend/gdpr/info",
            'use_frontend_cache' => false,
        ],
        'frontend-cache' => [
            'host' => getenv('YNFINITE_FRONTEND_CACHE_URL'),
            'port' => getenv('YNFINITE_FRONTEND_CACHE_PORT'),
        ],
        'form' => [
            'host' => $ynfinite_server,
            'port' => $ynfinite_port,
            'controller' => "/v1/cms/newfrontend/p/form/",
            "upload" => "/v1/cms/newfrontend/p/upload/",
            'gdpr' => "/v1/cms/newfrontend/gdpr/request",
            'gdprUpdate' => "/v1/cms/newfrontend/gdpr/update",
            'gdprDelete' => "/v1/cms/newfrontend/gdpr/delete"
        ],
        'sitemap' => [
            'host' => $ynfinite_server,
            'port' => $ynfinite_port,
            'controller' => "/v1/cms/newfrontend/p/sitemap"
        ],
        'robotsTxt' => [
            'host' => $ynfinite_server,
            'port' => $ynfinite_port,
            'controller' => "/v1/cms/newfrontend/p/robotstxt"
        ],
        'file' => [
            'host' => $fileservice_server,
            'port' => $fileservice_port
        ]
    ],
    "templateDir" => "templates",
    "auth" => [
        'api_key' => getenv('YN_API_KEY'),
        'service_id' => getenv('YN_SERVICE_ID'),    
    ],
    'dev' => getenv('DEV'),
    'static_pages' => getenv('STATIC_PAGES'),
];
