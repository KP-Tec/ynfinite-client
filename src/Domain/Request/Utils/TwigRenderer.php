<?php

namespace App\Domain\Request\Utils;

use Twig\Environment;
use Twig\TwigFilter;
use Twig\Extra\Intl\IntlExtension;
use \Twig\Extension\DebugExtension;
use Cocur\Slugify\Bridge\Twig\SlugifyExtension;
use Cocur\Slugify\Slugify;
use Psr\Container\ContainerInterface;

use App\Utils\Twig\Tokens\IsCookieActive;
use App\Utils\Twig\Tokens\GetCookieConsent;
use App\Utils\Twig\Tokens\IsScriptActive;
use App\Utils\Twig\TwigUtils;
use App\Utils\Twig\I18nUtils;

use App\Utils\Twig\FileSystemLoader;


final class TwigRenderer
{
    private $container;

    public function __construct(ContainerInterface $container) {        
        $this->container = $container;
    }

    public function initialize($data) {
        $this->initializeFileLoader($data);
        $this->initializePlugins($data);
    }

    public function initializeFileLoader($data) {
        $this->settings = $this->container->get("settings");

        $templateFolders = array();

        $templateFolders[] = getcwd(). "/../src/" . $this->settings["ynfinite"]["templateDir"];
        $templateFolders[] = getcwd() . '/../templates/';
        $templateFolders[] = getcwd() . '/../templates/' . $data["theme"]["namespace"];

        $addNamespaces = explode(",", $data["theme"]["additionalNamespaces"] ?? "");
        foreach($addNamespaces as $addNamespace) {
            $templateFolders[] = getcwd() . '/../templates/' . trim($addNamespace);
        }

        $loader = new FileSystemLoader($templateFolders, null, boolval($this->settings["ynfinite"]["debugTemplates"]));
        $rootPath = realpath(__DIR__);

        // $loader = new ArrayLoader($this->templates);
        $this->twig = new Environment($loader, ['debug' => true, /* 'cache' =>6 getcwd().'/../tmp/twig_cache', */]);

        $this->twig->addExtension(new IntlExtension());
        $this->twig->addExtension(new DebugExtension());
        $this->twig->addExtension(new SlugifyExtension(Slugify::create()));

        $this->twig->addGlobal("useragent", $this->getBrowserClasses());
    }

    public function initializePlugins($data, $templates, $overrideUrl = "") {
        $this->data = $data;
        $this->templates = $templates;

        $this->templateList = $this->generateTemplateList();
        $this->templateOverrides = $this->generateTemplateOverridesList();
        
        
        $this->uriData = $this->getURIData($overrideUrl);
        
        $this->twig->addTokenParser(new IsCookieActive($data));
        $this->twig->addTokenParser(new IsScriptActive($data));
        $this->twig->addTokenParser(new GetCookieConsent($data, $this->twig));

        $this->twig->addGlobal("templateList", $this->templateList);
        $this->twig->addGlobal("_templates", $this->templates);
        $this->twig->addGlobal('_ynfinite', $this->data);
        $this->twig->addGlobal("urlData", $this->uriData);

        $this->twigFunc = new TwigUtils($this->twig, $this->data, $this->templateList, $this->templateOverrides, $this->uriData);

        $_yfunc = new \Twig\TwigFunction('_yfunc', function ($context, $methode) {

            $arg_list = func_get_args();
            unset($arg_list[1]);

            return call_user_func_array(array($this->twigFunc, $methode), $arg_list);

        }, ['is_safe' => ['html'], "needs_context" => true]);

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
            return implode($seperator, $joinArray);
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

         $this->addFilterPlugins();
    }

    private function addFilterPlugins() {
        $path = getcwd() . "/../plugins/twig.php";
        if(file_exists($path)) {
            include($path);
            foreach($twigPlugins as $key => $value) {
                $this->twig->addFilter(new \Twig\TwigFilter($value["name"], $value["func"], $value["options"]));
            }
        }
    }

    public function renderPage() {
        $renderedPage = $this->twig->render($this->templateList['index'], $this->data);
        return $renderedPage;
    }

    public function renderTemplate($template) {
        $templateData = $this->data;
        $templateData["template"] = $this->templates[$template];
        $rendered = $this->twig->render($this->templateList[$template], $templateData);
        return $rendered;
    }

    private function generateTemplateOverridesList() {
        
        $overridePathes = array();
        $addOverrides = explode(",", $this->data["theme"]["additionalNamespaces"] ?? "");
        
        forEach( $addOverrides as $override) {
            $overridePathes[] = getcwd() . "/../" . $this->settings["ynfinite"]["templateDir"] . "/" . $override . "/tplConfig.php";
        }
        $overridePathes[] = getcwd() . "/../" . $this->settings["ynfinite"]["templateDir"] . "/" . $this->data["theme"]["namespace"] . "/tplConfig.php";
        
        $finalOverrides = array();
        forEach($overridePathes as $path) {
            if(file_exists($path)) {
                include($path);
                foreach($yn_templateOverrides as $key => $value) {
                    $finalOverrides[$key] = $value; 
                }
            }
        }
        
        return $finalOverrides;
    }

    private function generateTemplateList()
    {
        $templateArray = array();

        // $namespace = $this->data["theme"]["namespace"];

        foreach ($this->templates as $key => $template) {
            if(!$template["alias"]) {
                throw new \Exception("Template ".$template["frontend"]." is missing");
            }
            $templateArray[$template["frontend"]] = $template["alias"] . ".twig";
        }

        return $templateArray;
    }

    private function getBrowserClasses()
    {
        $useragent = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
        $uaArray = array();
        try {

            // $useragent = $provider->parse($_SERVER['HTTP_USER_AGENT']);

            $browserVersion = "";
            if($useragent->browser->version) {
                $browserVersion = $useragent->browser->version->toString();
            }

            $osVersion = "";
            if($useragent->os->version) {
                $osVersion = $useragent->os->version->toString();
            }

            $uaArray = array(
                $useragent->browser->toString(),
                $useragent->browser->toString() . "-" . $browserVersion,
                $useragent->engine->toString(),
                $useragent->os->toString(),
                $useragent->os->toString() . $osVersion,
                $useragent->device->type,
            );

            if ($useragent->device->model) {
                $uaArray[] = $useragent->device->model;

            }
            if ($useragent->device->brand ?? null) {
                $uaArray[] = $useragent->device->brand;
            }

        } catch (Exception $e) {

        }
        return implode(" ", $uaArray);
    }

    private function getURIData($overrideUrl)
	{
		$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		if($overrideUrl) {
			$baseUrl = $overrideUrl;
		}

		$path = explode('?', $baseUrl, 2);

		$listingURL = "";
		$perPageURL = "";
		if ($this->data["page"]["type"] === "listing") {
			$currentPage = $this->data["pagination"]["currentPage"] ?? null;
			$perPage = $this->data["pagination"]["perPage"] ?? null;

			$listingURL = $path[0]."?";
			$perPageURL = $path[0]."?";

			if (count($path) >= 2) {
				parse_str($path[1], $listingParams);
				unset($listingParams["__yPage"]);
				if (count($listingParams) > 0) {
					$listingURL .= http_build_query($listingParams);
					if(count($listingParams) >= 1) {
						$listingURL .= "&";
					}
				}

				parse_str($path[1], $perPageParams);
				unset($perPageParams["__yPerPage"]);
				if (count($perPageParams) > 0) {
					$perPageURL .= http_build_query($perPageParams);
					if(count($perPageParams) >= 1) {
						$perPageURL .= "&";
					}
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