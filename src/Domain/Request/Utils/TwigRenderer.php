<?php

namespace App\Domain\Request\Utils;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\Extra\Intl\IntlExtension;
use \Twig\Extension\DebugExtension;
use Cocur\Slugify\Bridge\Twig\SlugifyExtension;
use Cocur\Slugify\Slugify;
use UserAgentParser\Provider\WhichBrowser;
use Psr\Container\ContainerInterface;

use App\Utils\Twig\Tokens\IsCookieActive;
use App\Utils\Twig\Tokens\GetCookieConsent;
use App\Utils\Twig\Tokens\IsScriptActive;
use App\Utils\Twig\TwigUtils;
use App\Utils\Twig\I18nUtils;

final class TwigRenderer
{
    public function __construct(ContainerInterface $container) {        
        $this->settings = $container->get("settings");

        $loader = new FilesystemLoader([getcwd(). "/../src/" . $this->settings["ynfinite"]["templateDir"], getcwd() . '/../templates']);
        $rootPath = realpath(__DIR__);

        // $loader = new ArrayLoader($this->templates);
        $this->twig = new Environment($loader, ['debug' => true, /* 'cache' => getcwd().'/../tmp/twig_cache', */]);
        $this->twig->addExtension(new IntlExtension());
        $this->twig->addExtension(new DebugExtension());
        $this->twig->addExtension(new SlugifyExtension(Slugify::create()));

        $this->twig->addGlobal("useragent", $this->getBrowserClasses());
    }

    public function render($data, $templates) {
        $this->data = $data;
        $this->templates = $templates;

        $this->templateList = $this->generateTemplateList();
        $this->templateOverrides = $this->generateTemplateOverridesList();

        $this->uriData = $this->getURIData();

        $this->twig->addTokenParser(new IsCookieActive($data));
        $this->twig->addTokenParser(new IsScriptActive($data));
        $this->twig->addTokenParser(new GetCookieConsent($data, $this->twig));
        $this->twig->addGlobal("templateList", $this->templateList);
        $this->twig->addGlobal("_templates", $this->templates);
        $this->twig->addGlobal('_ynfinite', $this->data);
        $this->twig->addGlobal("urlData", $this->uriData);

        $this->twigFunc = new TwigUtils($this->twig, $this->data, $this->templateList, $this->templateOverrides, $this->uriData);

        $_yfunc = new \Twig\TwigFunction('_yfunc', function ($methode) {

            $arg_list = func_get_args();
            unset($arg_list[0]);

            return call_user_func_array(array($this->twigFunc, $methode), $arg_list);

        }, ['is_safe' => ['html']]);

        $this->twig->addFunction($_yfunc);

        $filterTrans = new \Twig\TwigFilter('trans', function ($string) {
            $i18n = new I18nUtils($this->twig, $this->data);
            return $i18n->translate($string);
        });

        $filterJoinBy = new \Twig\TwigFilter('joinBy', function ($array, $seperator, $field) {
            $joinArray = array();
            foreach($array as $item) {
                $joinArray[] = $item[$field];
            }
            return implode($joinArray, $seperator);
        });

        $filterHasCategory = new \Twig\TwigFilter('hasCategory', function ($content, $searchFor) {
            $joinArray = array();
           
            $categories = $content["settings"]["categories"];

            if(!$categories) 
                return false;

            $hasCategory = false;
            
            foreach($categories as $category) {    
                if($category["name"] === $searchFor) {
                    $hasCategory = true;
                    break;
                }
            }
            return $hasCategory;
        });

        $filterSome = new \Twig\TwigFilter('some', function ($content, $searchFor) {
           $result = false;

            foreach($searchFor as $search) {
                if($content[$search]) {
                    $result = true;
                    break;
                }
            }

            return $result;
        });

        $this->twig->addFilter($filterTrans);
        $this->twig->addFilter($filterJoinBy);
        $this->twig->addFilter($filterHasCategory);
        $this->twig->addFilter($filterSome);

        $renderedPage = $this->twig->render($this->templateList['index'], $data);
        return $renderedPage;

    }

    private function generateTemplateOverridesList() {
        $namespace = $this->data["theme"]["namespace"];
        if(file_exists(getcwd() . "/../" . $this->settings["ynfinite"]["templateDir"] . "/" . $namespace . "/tplConfig.php")) {
            include(getcwd() . "/../" . $this->settings["ynfinite"]["templateDir"] . "/" . $namespace . "/tplConfig.php");
            foreach($yn_templateOverrides as $key => $value) {
                $yn_templateOverrides[$key] = $namespace . "/" . $value; 
            }
            return $yn_templateOverrides;
        }
        return array();
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

            $listingURL = $path[0]."?";
            $perPageURL = $path[0]."?";

            if ($path[1]) {
                $paramsPagination = explode("&", $path[1]);

                if (($key = array_search("_y.page=$currentPage", $paramsPagination)) !== false) {
                    array_splice($paramsPagination, $key, 1);
                }

                if (count($paramsPagination) > 0) {
                    $listingURL .= implode("&", $paramsPagination) . "&";
                }

                if (($key = array_search("_y.perPage=$perPage", $paramsPagination)) !== false) {
                    array_splice($paramsPagination, $key, 1);
                }

                if (count($paramsPagination) > 0) {
                    $perPageURL .= implode("&", $paramsPagination) . "&";
                }
            } 
        }

        return array(
            "cleanURL" => $path[0],
            "URL" => $_SERVER['REQUEST_URI'],
            "listingURL" => $listingURL,
            "perPageURL" => $perPageURL
        );
    }

}