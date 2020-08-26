<?php

use Slim\App;

return function (App $app) {
    $app->get('/sitemap.xml', \App\Action\GetSitemapAction::class);
    $app->get('/{lang:.*}/sitemap.xml', \App\Action\GetSitemapAction::class);
    $app->get('/robots.txt', \App\Action\GetRobotsTxtAction::class);
    $app->get('/yn-updater/update', \App\Action\UpdateToVersionAction::class);
    $app->get('/yn-cache/invalidate', \App\Action\InvalidateCacheAction::class);
    $app->get('/yn-cache/invalidateAll', \App\Action\InvalidateAllCacheAction::class);
    $app->get('[/{params:.*}]', \App\Action\RenderPageAction::class);
    $app->post('[/{params:.*}]', \App\Action\SendFormAction::class);
};