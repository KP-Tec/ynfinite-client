<?php

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Middleware\ErrorMiddleware;
use SlimSession\Helper;

use Illuminate\Database\Capsule\Manager;

// Actions
use App\Action\GetRobotsTxtAction;
use App\Action\GetSitemapAction;
use App\Action\InvalidateCacheAction;
use App\Action\InvalidateAllCacheAction;
use App\Action\RenderPageAction;
use App\Action\SendFormAction;
use App\Action\UpdateToVersionAction;
use App\Action\GdprRequest;

// Services
use App\Domain\Request\Service\GetRobotsTxtService;
use App\Domain\Request\Service\GetSitemapService;
use App\Domain\Request\Service\RenderSitemapService;
use App\Domain\Request\Service\RenderPageService;
use App\Domain\Request\Service\RequestPageService;
use App\Domain\Request\Service\SendFormService;

// Utils
use App\Domain\Request\Utils\TwigRenderer;
use App\Domain\Handlers\HttpErrorHandler;
use App\Domain\Handlers\ShutdownHandler;

// Repository
use App\Domain\Request\Repository\RequestCacheRepository;

return [
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        $app = AppFactory::create();

        $displayErrorDetails = true;
        
        $callableResolver = $app->getCallableResolver();
        $responseFactory = $app->getResponseFactory();
        
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
        register_shutdown_function($shutdownHandler);

        return $app;
    },

    Helper::class => function (ContainerInterface $container) {
        return new Helper;
    },

    Manager::class => function (ContainerInterface $container) {
        $capsule = new Manager;
        $capsule->addConnection($container->get('settings')["ynfinite"]['db']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        if (!Manager::schema()->hasTable('static_cache')) {
            Manager::schema()->create('static_cache', function ($table) {
                $table->increments('id');
                $table->string('cache_key')->index();
                $table->text('filename');
                $table->text('type');
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
    InvalidateAllCacheAction::class => DI\autowire(),
    RenderPageAction::class => DI\autowire(),
    SendFormAction::class => DI\autowire(),
    UpdateToVersionAction::class => DI\autowire(),

    // Services
    GetRobotsTxtService::class => DI\autowire(),
    GetSitemapService::class => DI\autowire(),
    RenderSitemapService::class => DI\autowire(),
    RenderPageService::class => DI\autowire(),
    RequestPageService::class => DI\autowire(),
    SendFormService::class => DI\autowire(),

    // Repository
    RequestCacheRepository::class => DI\autowire(),

    // Utils
    TwigRenderer::class => DI\autowire(),
];