<?php

use Ypsolution\YnfinitePhpClient\Preferences;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => function (ContainerInterface $container): LoggerInterface {
        $preferences = $container->get(Preferences::class);

        $logger = new Logger('ynfinite');
        $logger->pushHandler(
            new RotatingFileHandler(
                $preferences->getRootPath() . '/logs/ynfinite.log'
            )
        );
        return $logger;
    },
];