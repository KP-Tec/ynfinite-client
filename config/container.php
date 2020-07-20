<?php

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use SlimSession\Helper;
use Slim\Views\Twig;

use Illuminate\Database\Capsule\Manager;

/*
use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory;
*/

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

    Manager::class => function (ContainerInterface $container) {
        $capsule = new Manager;
        $capsule->addConnection($container->get('settings')["ynfinite"]['db']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        /*
        $factory = new ConnectionFactory(new IlluminateContainer());

        $connection = $factory->make($container->get('settings')["ynfinite"]['db']);

        // Disable the query log to prevent memory issues
        $connection->disableQueryLog();
        */

        if (!Manager::schema()->hasTable('static_page_cache')) {
            Manager::schema()->create('static_page_cache', function ($table) {
                $table->increments('id');
                $table->string('cache_key')->unique();
                $table->string('filename')->unique();
                $table->timestamps();
            });
        }

        return $capsule;
    },


    PDO::class => function (ContainerInterface $container) {
        return $container->get(Connection::class)->getPdo();
    },

    Twig::class => function (ContainerInterface $container) {
        return Twig::create(getcwd() . '/../src/templates',
            [
                'cache' => getcwd() . '/../tmp/cache',
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