<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$ynfinite_server = 'https://live-server.ynfinite.de';
$ynfinite_port = 443;
$fileservice_server = 'https://live-files.ynfinite.de';
$fileservice_port = 443;
if ($_ENV['DEV'] !== "false") {
    $ynfinite_server = 'https://ynfinite-node';
    $ynfinite_port = 4242;
    $fileservice_server = 'http://imageservice';
    $fileservice_port = 3333;
}

return [
    "debugTemplates" => toBoolean($_ENV["DEBUG_TEMPLATES"] ?? false),
    'pageTypes' => array("html", "htm"),
    "db" => array(
        'driver' => 'mysql',
        'host' => $_ENV["DB_HOST"],
        'username' => $_ENV["DB_USER"],
        'database' => $_ENV["DB_NAME"],
        'password' => $_ENV["DB_PASSWORD"],
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
            'controller' => "/v1/api/frontend/form_new",
        ],
        'api/getContent' => [
            'host' => $ynfinite_server,
            'port' => $ynfinite_port,
            'controller' => "/v1/api/yn-api/content",
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
        'api_key' => $_ENV['YN_API_KEY'],
        'service_id' => $_ENV['YN_SERVICE_ID'],
    ],
    'dev' => $_ENV['DEV'],
    'static_pages' => $_ENV['STATIC_PAGES'],
    'static_requests' => $_ENV['STATIC_REQUESTS'],
];
