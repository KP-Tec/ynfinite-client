<?php

use DI\ContainerBuilder;
use Slim\App;

function toBoolean($value) {
    if(!$value || $value === "false") return false;
    return true;
}

$dotenv = Dotenv\Dotenv::create(__DIR__ . "/../");
$dotenv->load();

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '/container.php');

// Build PHP-DI Container instance
$containerBuilder->enableCompilation(__DIR__ . '/../tmp/cache');
if(toBoolean(getenv("ENABLE_APCU"))) {
    $containerBuilder->enableDefinitionCache();
}

$container = $containerBuilder->build();

// Create App instance
$app = $container->get(App::class);

// Register routes
(require __DIR__ . '/routes.php')($app);

// Register middleware
(require __DIR__ . '/middleware.php')($app);

return $app;