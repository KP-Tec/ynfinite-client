<?php

namespace App\Domain\Request\Utils;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extra\Intl\IntlExtension;
use \Twig\Extension\DebugExtension;
use Cocur\Slugify\Bridge\Twig\SlugifyExtension;
use Cocur\Slugify\Slugify;
use UserAgentParser\Provider\WhichBrowser;
use Psr\Container\ContainerInterface;

use App\Utils\Twig\Tokens\IsCookieActive;
use App\Utils\Twig\Tokens\GetCookieConsent;
use App\Utils\Twig\TwigUtils;

final class TwigRenderer
{
    public function __construct(ContainerInterface $container) {        
        $this->settings = $container->get("settings");

        $loader = new FilesystemLoader([getcwd(). "/../src/" . $this->settings["ynfinite"]["templateDir"], getcwd() . '/../templates']);
        $rootPath = realpath(__DIR__);

        // $loader = new ArrayLoader($this->templates);
        $this->twig = new Environment($loader, ['debug' => true, 'cache' => getcwd().'/../tmp/twig_cache',]);
        $this->twig->addExtension(new IntlExtension());
        $this->twig->addExtension(new DebugExtension());
        $this->twig->addExtension(new SlugifyExtension(Slugify::create()));

        $this->twig->addGlobal("useragent", $this->getBrowserClasses());
        

    }

    public function render($data, $templates) {
        $this->data = $data;
        $this->templates = $templates;

        $this->templateList = $this->generateTemplateList();

        var_dump($this->templateList);

        $this->uriData = $this->getURIData();

        $this->twig->addTokenParser(new IsCookieActive($data));
        $this->twig->addTokenParser(new GetCookieConsent($data, $this->twig));
        $this->twig->addGlobal("templateList", $this->templateList);
        $this->twig->addGlobal("_templates", $this->templates);
        $this->twig->addGlobal('_ynfinite', $this->data);
        $this->twig->addGlobal("urlData", $this->uriData);

        $_yfunc = new \Twig\TwigFunction('_yfunc', function ($methode) {
            $func = new TwigUtils($this->twig, $this->data, $this->templateList, $this->uriData);

            $arg_list = func_get_args();
            unset($arg_list[0]);

            return call_user_func_array(array($func, $methode), $arg_list);

        }, ['is_safe' => ['html']]);

        $this->twig->addFunction($_yfunc);

        $renderedPage = $this->twig->render($this->templateList['index'], $data);
        return $renderedPage;

    }

    private function generateTemplateList()
    {
        $templateArray = array();

        $namespace = $this->data["theme"]["namespace"];

        foreach ($this->templates as $key => $template) {
            if(!$template["alias"]) {
                throw new \Exception("Template ".$template["frontend"]." is missing");
            }
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

    private function getURIData()
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

}