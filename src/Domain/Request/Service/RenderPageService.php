<?php

namespace App\Domain\Request\Service;

use SlimSession\Helper as SessionHelper;
use App\Domain\Request\Repository\RequestCacheRepository;

use Psr\Container\ContainerInterface;
use App\Domain\Request\Utils\TwigRenderer;
use App\Utils\Twig\TwigUtils;
use App\Utils\Cache\StaticCache;

final class RenderPageService
{

    private $repository;

    public function __construct(TwigRenderer $twig, RequestCacheRepository $repository, ContainerInterface $container) {
        $this->repository = $repository;
        $this->settings = $container->get("settings")["ynfinite"];
        $this->twig = $twig;
    }

    public function render($templates, $data, $cacheUrl) {

        $this->twig->initializeFileLoader($data);
        $this->twig->initializePlugins($data, $templates, $cacheUrl);
        
        $renderedPage = $this->twig->renderPage();
        
        return $renderedPage;

    }

    public function renderTemplate($templates, $data, $template, $baseUrl) {
        $this->twig->initializeFileLoader($data);
        $this->twig->initializePlugins($data, $templates, $baseUrl);

        $rendered = $this->twig->renderTemplate($template, $data);

        return $rendered;
    }

}