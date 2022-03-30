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
            'controller' => "/v1/api/frontend/page"
        ],
        'form' => [
            'host' => $ynfinite_server,
            'port' => $ynfinite_port,
            'controller' => "/v1/api/frontend/form",
        ],
        'gdpr_request' => [
            'host' => $ynfinite_server,
            'port' => $ynfinite_port,
            'controller' => "/v1/api/gdpr/request",
        ],
        'gdpr_update' => [
            'host' => $ynfinite_server,
            'port' => $ynfinite_port,
            'controller' => "/v1/api/gdpr/update",
        ],
        'sitemap' => [
            'host' => $ynfinite_server,
            'port' => $ynfinite_port,
            'controller' => "/v1/api/frontend/sitemap"
        ],
        'robots' => [
            'host' => $ynfinite_server,
            'port' => $ynfinite_port,
            'controller' => "/v1/api/frontend/robots"
        ],
        'gdpr' => [
            "request" => [
                'host' => $ynfinite_server,
                'port' => $ynfinite_port,
                'controller' => "/v1/api/frontend/gdpr/request"
            ]
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
    'static_requests' => getenv('STATIC_REQUESTS'),
];
