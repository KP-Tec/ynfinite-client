<?php

namespace App\Domain\Request\Service;

use SlimSession\Helper as SessionHelper;
use App\Domain\Request\Repository\RequestCacheRepository;

use Psr\Container\ContainerInterface;
use App\Domain\Request\Utils\TwigRenderer;
use App\Utils\Twig\TwigUtils;
use App\Utils\Cache\StaticPageCache;

final class RenderPageService
{

    private $repository;

    public function __construct(TwigRenderer $twig, RequestCacheRepository $repository, ContainerInterface $container) {
        $this->repository = $repository;
        $this->settings = $container->get("settings")["ynfinite"];
        $this->twig = $twig;
    }

    public function render($templates, $data) {

        $this->twig->initializeFileLoader($data);
        
        $renderedPage = $this->twig->render($data, $templates);

        if(filter_var($this->settings["static_pages"], FILTER_VALIDATE_BOOLEAN) === true) {
            $filename = StaticPageCache::createStaticPage($renderedPage, $data["page"]["type"]);
            if($filename) {
                $this->repository->createCache($filename, $data["cacheKey"]);
            }
        }
        
        return $renderedPage;

    }

}