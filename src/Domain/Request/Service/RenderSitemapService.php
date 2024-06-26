<?php

namespace App\Domain\Request\Service;

use SlimSession\Helper as SessionHelper;
use App\Domain\Request\Repository\RequestCacheRepository;

use Psr\Container\ContainerInterface;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extra\Intl\IntlExtension;
use \Twig\Extension\DebugExtension;
use Cocur\Slugify\Bridge\Twig\SlugifyExtension;
use Cocur\Slugify\Slugify;



final class RenderSitemapService
{

    private $repository;
    public $settings;

    public function __construct(ContainerInterface $container) {
        $this->settings = $container->get("settings");

        $loader = new FilesystemLoader([getcwd(). "/../src/" . $this->settings["ynfinite"]["templateDir"], getcwd() . '/../templates']);
        $this->twig = new Environment($loader, ['debug' => true, /* 'cache' => getcwd().'/../tmp/twig_cache', */]);
        $this->twig->addExtension(new IntlExtension());
        $this->twig->addExtension(new DebugExtension());
        $this->twig->addExtension(new SlugifyExtension(Slugify::create()));
    }

    public function render($sitemap) {
        $renderedSitemap = $this->twig->render("yn/module/sitemap/index.twig", array("sitemap" => $sitemap));
        return $renderedSitemap;
    }

}