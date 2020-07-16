<?php

namespace App\Domain\Request\Service;

use Psr\Container\ContainerInterface;
use SlimSession\Helper as SessionHelper;
use App\Domain\Request\Repository\RequestCacheRepository;

use App\Domain\Request\Utils\TwigRenderer;
use App\Utils\Twig\TwigUtils;
use App\Utils\Cache\StaticPageCache;

final class RenderPageService
{

    private $respository;

    public function __construct(ContainerInterface $container) {
        $this->settings = $container->get("settings");

        $this->twig = new TwigRenderer($settings["ynfinite"]);
    }

    private function generateFileList($templates)
    {
        $templateArray = array();

        $namespace = $this->data["theme"]["namespace"];

        foreach ($templates as $key => $template) {
            $templateArray[$template["frontend"]] = $namespace . "/" . $template["alias"] . ".twig";
        }

        return $templateArray;
    }


    public function render($templates, $data) {

        $renderedPage = $this->twig->render($data, $$templates);

        if(filter_var($this->settings["static_pages"], FILTER_VALIDATE_BOOLEAN) === true) {
            StaticPageCache::createStaticPage($renderedPage, $data["page"]["type"]);
        }
        
        return $renderedPage;

    }

}