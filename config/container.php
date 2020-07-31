<?php

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use SlimSession\Helper;

use Illuminate\Database\Capsule\Manager;

// Actions
use App\Action\GetRobotsTxtAction;
use App\Action\GetSitemapAction;
use App\Action\InvalidateCacheAction;
use App\Action\RenderPageAction;
use App\Action\SendFormAction;
use App\Action\UpdateToVersionAction;

// Services
use App\Domain\Request\Service\GetRobotsTxtService;
use App\Domain\Request\Service\GetSitemapService;
use App\Domain\Request\Service\RenderPageService;
use App\Domain\Request\Service\RequestPageService;
use App\Domain\Request\Service\SendFormService;

// Utils
use App\Domain\Request\Utils\TwigRenderer;

// Repository
use App\Domain\Request\Repository\RequestCacheRepository;

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

    // Actions
    GetRobotsTxtAction::class => DI\autowire(),
    GetSitemapAction::class => DI\autowire(),
    InvalidateCacheAction::class => DI\autowire(),
    RenderPageAction::class => DI\autowire(),
    SendFormAction::class => DI\autowire(),
    UpdateToVersionAction::class => DI\autowire(),

    // Services
    GetRobotsTxtService::class => DI\autowire(),
    GetSitemapService::class => DI\autowire(),
    RenderPageService::class => DI\autowire(),
    RequestPageService::class => DI\autowire(),
    SendFormService::class => DI\autowire(),

    // Repository
    RequestCacheRepository::class => DI\autowire(),

    // Utils
    TwigRenderer::class => DI\autowire()
];