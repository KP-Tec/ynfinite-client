<?php

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use SlimSession\Helper;
use Slim\Views\Twig;

return [
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        return AppFactory::create();
    },

    Helper::class => function (ContainerInterface $container) {
        return new Helper;
    },

    Twig::class => function (ContainerInterface $container) {
        return Twig::create(getcwd() . '/src/templates',
            [
                'cache' => getcwd() . '/tmp/cache',
                'auto_reload' => true,
                'debug' => false,
            ]
        );
    },

    ErrorMiddleware::class => function (ContainerInterface $container) {
        $app = $container->get(App::class);
        $settings = $container->get('settings')['error'];

        return new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool)$settings['display_error_details'],
            (bool)$settings['log_errors'],
            (bool)$settings['log_error_details']
        );
    },

];