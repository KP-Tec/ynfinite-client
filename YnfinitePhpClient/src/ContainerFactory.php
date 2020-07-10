<?php

namespace Ypsolution\YnfinitePhpClient;

use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerInterface;

class ContainerFactory
{
    /**
     * @param string $rootPath
     *
     * @return ContainerInterface
     * @throws Exception
     */
    public static function create(string $rootPath, string $templatePath): ContainerInterface
    {
        $containerBuilder = new ContainerBuilder();

        $config = require $rootPath.'/yn-config.php';
        $config['client'] = array('templatePath' => $templatePath);
        $containerBuilder->addDefinitions([
            Preferences::class => new Preferences($rootPath, $config),
        ]);

        $containerBuilder->addDefinitions($rootPath . '/config/container-definitions.php');
        $containerBuilder->addDefinitions($rootPath . '/config/container-controllers.php');
        // $containerBuilder->enableCompilation($rootPath . '/cache');

        return $containerBuilder->build();
    }
}