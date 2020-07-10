<?php

/* DO NOT TOUCH ANYTHING BEYOND THIS POINT IF YOU DO NOT KNOW WHAT YOU ARE DOING! */

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
    'ynfinite' => [
        'installPassword' => getenv('YN_INSTALL_PASSWORD'),
        'pageTypes' => array("html", "htm"),
        'services' => [
            'frontend' => [
                'host' => $ynfinite_server,
                'port' => $ynfinite_port,
                'controller' => "/v1/cms/frontend/p/",
                'gdprInfo' => "/v1/cms/frontend/gdpr/info",
                'use_frontend_cache' => false,
                'frontend_cache_url' => getenv('YNFINITE_FRONTEND_CACHE_URL'),
                'frontend_cache_port' => getenv('YNFINITE_FRONTEND_CACHE_PORT')
            ],
            'form' => [
                'host' => $ynfinite_server,
                'port' => $ynfinite_port,
                'controller' => "/v1/cms/frontend/p/form/",
                'gdpr' => "/v1/cms/frontend/gdpr/request",
                'gdprUpdate' => "/v1/cms/frontend/gdpr/update",
                'gdprDelete' => "/v1/cms/frontend/gdpr/delete"
            ],
            'sitemap' => [
                'host' => $ynfinite_server,
                'port' => $ynfinite_port,
                'controller' => "/v1/cms/frontend/p/sitemap"
            ],
            'robotsTxt' => [
                'host' => $ynfinite_server,
                'port' => $ynfinite_port,
                'controller' => "/v1/cms/frontend/p/robotstxt"
            ],
            'file' => [
                'host' => $fileservice_server,
                'port' => $fileservice_port
            ]
        ],
        "templateDir" => "web/templates",
        "settings" => [
            'api_key' => getenv('YN_API_KEY'),
            'service_id' => getenv('YN_SERVICE_ID'),
            'dev' => getenv('DEV'),
        ]
    ],
];
