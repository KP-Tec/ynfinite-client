<?php

namespace App\Domain\Request\Service;

use App\Domain\Request\Repository\RequestCacheRepository;

use Psr\Container\ContainerInterface;
use App\Utils\Cache\StaticCache;

final class CacheService
{

    private $repository;

    public function __construct(RequestCacheRepository $repository, ContainerInterface $container) {
        $this->repository = $repository;
        $this->settings = $container->get("settings")["ynfinite"];
    }

    public function createCache($key, $content) {

        if(filter_var($this->settings["static_pages"], FILTER_VALIDATE_BOOLEAN) === true) {
            $filename = StaticCache::createCache($key, $content);
            if($filename) {
                $this->repository->createCache($key, $filename);
            }
        }

        return $content;

    }
}