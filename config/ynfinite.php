<?php

$dotenv = Dotenv\Dotenv::create(getcwd());
$dotenv->load();

$ynfinite_server = 'https://live-server.ynfinite.de';
$ynfinite_port = 443;
$fileservice_server = 'https://live-files.ynfinite.de';
$fileservice_port = 443;
if (getenv('DEV')) {
    $ynfinite_server = 'https://ynfinite-node';
    $ynfinite_port = 4242;
    $fileservice_server = 'http://imageservice';
    $fileservice_port = 3333;
}


return [
    'installPassword' => getenv('YN_INSTALL_PASSWORD'),
    'pageTypes' => array("html", "htm"),
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
    "templateDir" => "templates/yn",
    "auth" => [
        'api_key' => getenv('YN_API_KEY'),
        'service_id' => getenv('YN_SERVICE_ID'),    
    ],
    'dev' => getenv('DEV'),
    'static_pages' => getenv('STATIC_PAGES'),
];
