<?php

use Ypsolution\YnfinitePhpClient\controller\Install;
use Ypsolution\YnfinitePhpClient\controller\Frontend;
use Ypsolution\YnfinitePhpClient\controller\Ynfinite;
use Ypsolution\YnfinitePhpClient\Preferences;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    Install::class => function (ContainerInterface $container): Install {
        return new Install($container->get('view'), $container->get("session"), $container->get(Preferences::class));
    },
    Frontend::class => function (containerInterface $container): Frontend {
        return new Frontend($container->get("view"), $container->get("session"), $container->get(Preferences::class));
    },
    Ynfinite::class => function (containerInterface $container): Ynfinite {
        return new Ynfinite($container->get("view"), $container->get("session"), $container->get(Preferences::class));
    }
];