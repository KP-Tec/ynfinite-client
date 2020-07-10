<?php
namespace Ypsolution\YnfinitePhpClient;

use Slim\Middleware\Session;
use SlimSession\Helper;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Ypsolution\YnfinitePhpClient\controller\Frontend;
use Ypsolution\YnfinitePhpClient\controller\Install;
use Ypsolution\YnfinitePhpClient\controller\Ynfinite;

class YnfiniteClient
{

    public static function postPackageInstall(Event $event)
    {
        $composer = $event->getComposer();
        $vendorPath = $composer->getConfig()->get('vendor-dir');
        if (!file_exists($vendorPath.'../cache')) {
            mkdir($vendorPath.'../cache', 0755, true);
        }
    }

    public static function create(string $templatePath)
    {
        $projectRootPath = getcwd();
        $rootPath = realpath(__DIR__);

        $container = ContainerFactory::create($rootPath, $templatePath);
        $container->set('session', function () {
            return new Helper;
        });

        AppFactory::setContainer($container);

        $container->set('view', function () use ($projectRootPath) {
            return Twig::create(__DIR__ . '/templates',
                [
                    'cache' => $projectRootPath.'/cache',
                    'auto_reload' => true,
                    'debug' => false,
                ]);
        });

        $app = AppFactory::create();
        $app->getRouteCollector()->setCacheFile(
            $projectRootPath.'/cache/routes.cache'
        );
        $app->addRoutingMiddleware();
        $app->add(TwigMiddleware::createFromContainer($app));
        $app->add(new Session([
            'name' => 'ynfinite-session',
            'autorefresh' => true,
            'lifetime' => '1 hour'
        ]));
        $app->addErrorMiddleware(true, true, false);
        $app->group('/ynfinite', function (RouteCollectorProxy $group) {
            $group->get('/install', Install::class . ":index")->setName('install');
            $group->post('/install', Install::class . ":save")->setName('install_save');
            $group->get('/install/password', Install::class . ":password")->setName('password_protected');
            $group->post('/install/password/check', Install::class . ":checkPassword")->setName('password_check');
            $group->get('/install/success', Install::class . ":success")->setName('install_save');

        });

        $app->get('/sitemap.xml', Ynfinite::class . ":index")->setName("sitemap");
        $app->get('/robots.txt', Ynfinite::class . ":robotsTxt")->setName("robotsTxt");
        $app->get('[/{params:.*}]', Frontend::class . ":index")->setName("frontend");
        $app->post('[/{params:.*}]', Frontend::class . ":send")->setName("frontend-send");

        return $app;
    }
}



