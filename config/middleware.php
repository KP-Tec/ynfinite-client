<?php

use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use Slim\Views\TwigMiddleware;
use Slim\Middleware\Session;

return function (App $app) {
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    $app->getRouteCollector()->setCacheFile(
        __DIR__.'/../tmp/cache/routes.cache'
    );

    // Add the Slim built-in routing middleware
    $app->addRoutingMiddleware();

  //  $app->add(TwigMiddleware::createFromContainer($app));

    $app->add(new Session([
        'name' => 'ynfinite-session',
        'autorefresh' => true,
        'lifetime' => '1 hour'
    ]));

    // Catch exceptions and errors
    $app->add(ErrorMiddleware::class);
};