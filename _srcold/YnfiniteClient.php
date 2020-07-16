<?php
namespace Ypsolution\YnfinitePhpClient;

use SlimSession\Helper;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\Twig;
use Ypsolution\YnfinitePhpClient\controller\Frontend;
use Ypsolution\YnfinitePhpClient\controller\Install;
use Ypsolution\YnfinitePhpClient\controller\Ynfinite;
use Ypsolution\YnfinitePhpClient\StaticPageCache;

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
        
       

        return $app;
    }
}



