<?php

namespace Ypsolution\YnfinitePhpClient\utils;

use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extra\Intl\IntlExtension;
use UserAgentParser\Provider\WhichBrowser;
use Cocur\Slugify\Bridge\Twig\SlugifyExtension;
use Cocur\Slugify\Slugify;
use Exception;
use Ypsolution\YnfinitePhpClient\utils\TwigUtils;

use Ypsolution\YnfinitePhpClient\utils\tokens\IsCookieActive;
use Ypsolution\YnfinitePhpClient\utils\tokens\GetCookieConsent;
use Ypsolution\YnfinitePhpClient\StaticPageCache;

class RenderUtils
{

    private $templates;
    private $data;


    public function __construct($config, $templates, $data)
    {
        $this->config = $config;
        $this->data = $data;
        $this->templates = $templates;
    }



    private function generateFileList()
    {
        $templateArray = array();

        $namespace = $this->data["theme"]["namespace"];

        foreach ($this->templates as $key => $template) {
            $templateArray[$template["frontend"]] = $namespace . "/" . $template["alias"] . ".twig";
        }

        return $templateArray;
    }

    public function getURIData()
    {
        $path = explode('?', $_SERVER['REQUEST_URI'], 2);

        $listingSeparator = "?";
        $perPageSeparator = "?";

        $listingURL = "";
        if ($this->data["page"]["type"] === "listing") {
            $currentPage = $this->data["pagination"]["currentPage"];
            $perPage = $this->data["pagination"]["perPage"];

            $listingURL = $path[0];
            $perPageURL = $path[0];

            if ($path[1]) {
                $paramsPagination = explode("&", $path[1]);

                if (($key = array_search("_y.page=$currentPage", $paramsPagination)) !== false) {
                    array_splice($paramsPagination, $key, 1);
                }

                if (count($paramsPagination) > 0) {
                    $listingURL .= "?" . implode("&", $paramsPagination) . "&";
                }

                if (($key = array_search("_y.perPage=$perPage", $paramsPagination)) !== false) {
                    array_splice($paramsPagination, $key, 1);
                }

                if (count($paramsPagination) > 0) {
                    $perPageURL .= "?" . implode("&", $paramsPagination) . "&";
                } else {
                    $perPageURL .= "?";
                }
            } else {
                $listingURL .= "?";
                $perPageURL .= "?";
            }
        }

        return array(
            "cleanURL" => $path[0],
            "URL" => $_SERVER['REQUEST_URI'],
            "separator" => $separator,
            "listingURL" => $listingURL,
            "perPageURL" => $perPageURL
        );
    }

    public function render()
    {

        $this->fileList = $this->generateFileList();

        $loader = new FilesystemLoader([__DIR__ . '/../' . $this->config["ynfinite"]["templateDir"], getcwd() . '/' . $this->config["client"]["templatePath"]]);
        $rootPath = realpath(__DIR__);

        // $loader = new ArrayLoader($this->templates);
        $this->twig = new Environment($loader, ['debug' => true]);
        $this->twig->addExtension(new IntlExtension());
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        $this->twig->addExtension(new SlugifyExtension(Slugify::create()));

        $this->twig->addTokenParser(new IsCookieActive($this->data));
        $this->twig->addTokenParser(new GetCookieConsent($this->data, $this->twig));

        $this->twig->addGlobal("templateList", $this->fileList);
        $this->twig->addGlobal("useragent", $this->getBrowserClasses());
        $this->twig->addGlobal('_ynfinite', $this->data);
        $this->twig->addGlobal("_templates", $this->templates);
        $this->twig->addGlobal("urlData", $this->getURIData());


        $_yfunc = new \Twig\TwigFunction('_yfunc', function ($methode) {

            $func = new TwigUtils($this->twig, $this->data, $this->fileList, $this->getURIData());

            $arg_list = func_get_args();
            unset($arg_list[0]);

            return call_user_func_array(array($func, $methode), $arg_list);

        }, ['is_safe' => ['html']]);

        $this->twig->addFunction($_yfunc);

//        return $this->twig->render($this->fileList['index'], $this->data);
        $renderedPage = $this->twig->render($this->fileList['index'], $this->data);


        StaticPageCache::createStaticPage($renderedPage, $this->data["page"]["type"]);
        return $renderedPage;
    }

    public function renderToolbar()
    {

        $loader = new FilesystemLoader('src/yn/templates');
        $twig = new Environment($loader, ['debug' => true]);
        $twig->addExtension(new IntlExtension());
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addExtension(new SlugifyExtension(Slugify::create()));
        $twig->addGlobal("useragent", $this->getBrowserClasses());
        $twig->addGlobal('_ynfinite', $this->data["_ynfinite"]);
        $twig->addGlobal('_yfunc', new TwigUtils($twig, $this->data));

        return $twig->render('toolbar.twig', $this->data);
    }


    public function getBrowserClasses()
    {
        $provider = new WhichBrowser();
        $uaArray = array();
        try {

            $useragent = $provider->parse($_SERVER['HTTP_USER_AGENT']);

            $uaArray = array(
                $useragent->getBrowser()->getName(),
                $useragent->getBrowser()->getName() . "-" . $useragent->getBrowser()->getVersion()->getComplete(),
                $useragent->getRenderingEngine()->getName(),
                $useragent->getOperatingSystem()->getName(),
                $useragent->getOperatingSystem()->getName() . $useragent->getOperatingSystem()->getVersion()->getComplete(),
                $useragent->getDevice()->getType(),
            );

            if ($useragent->getDevice()->getModel()) {
                $uaArray[] = $useragent->getDevice()->getModel();

            }
            if ($useragent->getDevice()->getBrand()) {
                $uaArray[] = $useragent->getDevice()->getBrand();
            }

        } catch (Exception $e) {

        }
        return implode(" ", $uaArray);
    }
}