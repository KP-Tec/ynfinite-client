<?php

namespace App\Domain\Request\Utils;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extra\Intl\IntlExtension;
use \Twig\Extension\DebugExtension;
use Cocur\Slugify\Bridge\Twig\SlugifyExtension;
use Cocur\Slugify\Slugify;

use App\Utils\Twig\Tokens\IsCookieActive;
use App\Utils\Twig\Tokens\GetCookieConsent;

final class TwigRenderer
{
    public function __construct($settings) {
        $this->settings = $settings;
        
        $loader = new FilesystemLoader([__DIR__ . '/../' . $this->settings["templateDir"], getcwd() . '/templates']);
        $rootPath = realpath(__DIR__);

        // $loader = new ArrayLoader($this->templates);
        $this->twig = new Environment($loader, ['debug' => true]);
        $this->twig->addExtension(new IntlExtension());
        $this->twig->addExtension(new DebugExtension());
        $this->twig->addExtension(new SlugifyExtension(Slugify::create()));

        $this->twig->addGlobal("useragent", $this->getBrowserClasses());
        

    }

    public function render($data, $templates) {
        $templateList = $this->generateTemplateList($templates);

        $uriData = $this->getURIData($data);

        $this->twig->addTokenParser(new IsCookieActive($data));
        $this->twig->addTokenParser(new GetCookieConsent($data, $this->twig));
        $this->twig->addGlobal("templateList", $templateList);
        $this->twig->addGlobal("_templates", $templates);
        $this->twig->addGlobal('_ynfinite', $data);
        $this->twig->addGlobal("urlData", $uriData);

        $_yfunc = new \Twig\TwigFunction('_yfunc', function ($methode) {

            $func = new TwigUtils($this->twig, $data, $templateList, $uriData);

            $arg_list = func_get_args();
            unset($arg_list[0]);

            return call_user_func_array(array($func, $methode), $arg_list);

        }, ['is_safe' => ['html']]);

        $this->twig->addFunction($_yfunc);

        $renderedPage = $this->twig->render($templateList['index'], $data);
        return $renderedPage;

    }

    private function generateTemplateList()
    {
        $templateArray = array();

        $namespace = $this->data["theme"]["namespace"];

        foreach ($this->templates as $key => $template) {
            $templateArray[$template["frontend"]] = $namespace . "/" . $template["alias"] . ".twig";
        }

        return $templateArray;
    }

    private function getBrowserClasses()
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

    private function getURIData($data)
    {
        $path = explode('?', $_SERVER['REQUEST_URI'], 2);

        $listingSeparator = "?";
        $perPageSeparator = "?";

        $listingURL = "";
        if ($data["page"]["type"] === "listing") {
            $currentPage = $data["pagination"]["currentPage"];
            $perPage = $data["pagination"]["perPage"];

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

}