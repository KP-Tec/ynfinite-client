<?php

use Slim\App;

return function (App $app) {
    
    /*
    $app->group('/ynfinite', function (RouteCollectorProxy $group) {
        $group->get('/install', Install::class . ":index")->setName('install');
        $group->post('/install', Install::class . ":save")->setName('install_save');
        $group->get('/install/password', Install::class . ":password")->setName('password_protected');
        $group->post('/install/password/check', Install::class . ":checkPassword")->setName('password_check');
        $group->get('/install/success', Install::class . ":success")->setName('install_save');

    });
    */

    $app->get('/sitemap.xml', \App\Action\GetSitemapAction::class);
    $app->get('/robots.txt', \App\Action\GetRobotsTxtAction::class);
    $app->get('/yn-cache/invalidate', \App\Action\InvalidateCacheAction::class);
    $app->get('[/{params:.*}]', \App\Action\RenderPageAction::class);
    $app->post('[/{params:.*}]', \App\Action\SendFormAction::class);
};