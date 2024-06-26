<?php

namespace App\Domain\Request\Service;

use App\Domain\Request\Repository\RequestCacheRepository;

use Psr\Container\ContainerInterface;
use App\Utils\Cache\StaticCache;

final class CacheService
{
    private $repository;
    public $settings;

    public function __construct(RequestCacheRepository $repository, ContainerInterface $container) {
        $this->repository = $repository;
        $this->settings = $container->get("settings")["ynfinite"];
    }

    public function createCache($type, $content) {
        if(filter_var($this->settings["static_pages"], FILTER_VALIDATE_BOOLEAN) === true) {
            $filename = StaticCache::createCache($type, $content);
            if($filename) {
                $key = StaticCache::createCacheKey($type, true);
                $this->repository->createCache($key, $filename);
            }
        }

        return $content;

    }
}