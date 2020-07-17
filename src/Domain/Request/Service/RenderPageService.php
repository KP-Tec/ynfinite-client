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

    private $respository;

    public function __construct(TwigRenderer $twig, ContainerInterface $container) {
        $this->settings = $container->get("settings")["ynfinite"];
        $this->twig = $twig;
    }

    public function render($templates, $data) {

        $renderedPage = $this->twig->render($data, $templates);

        
        if(filter_var($this->settings["static_pages"], FILTER_VALIDATE_BOOLEAN) === true) {
            var_dump("CREATING STATIC PAGE");
            StaticPageCache::createStaticPage($renderedPage, $data["page"]["type"]);
        }
        
        return $renderedPage;

    }

}