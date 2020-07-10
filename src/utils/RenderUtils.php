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

class RenderUtils {

    private $templates;
    private $data;


    public function __construct($config, $templates, $data) {
        $this->config = $config;
        $this->data = $data;
        $this->templates = $templates;
    }

    private function generateFileList() {
        $templateArray = array();

        $namespace = $this->data["theme"]["namespace"];

        foreach($this->templates as $key => $template) {
            $templateArray[$template["frontend"]] = $namespace."/".$template["alias"].".twig";
        }

        return $templateArray;
    }

    public function getURIData() {
        $path = explode('?', $_SERVER['REQUEST_URI'], 2);

        $separator = "?";
        if(count($path) > 1) {
            $separator = "&";
        }

        $listingURL = "";
        if($this->data["page"]["type"] === "listing") {
            $currentPage = $this->data["pagination"]["currentPage"];

            $params = explode("&", $path[1]);
            if (($key = array_search("_y.page=$currentPage", $params)) !== false) {
                unset($params[$key]);
            }

            $listingURL = $path[0].implode("&", $params);
        }

        return array(
            "cleanURL" => $path[0],
            "URL" => $_SERVER['REQUEST_URI'],
            "separator" => $separator,
            "listingURL" => $listingURL
        );
    }

    public function render(){

        $this->fileList = $this->generateFileList();

        $loader = new \Twig\Loader\FilesystemLoader([__DIR__.'/../'.$this->config["ynfinite"]["templateDir"], getcwd().'/'.$this->config["client"]["templatePath"]]);



        // $loader = new ArrayLoader($this->templates);
        $this->twig = new Environment($loader, ['debug' => true]);
        $this->twig->addExtension(new IntlExtension());
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        $this->twig->addExtension(new SlugifyExtension(Slugify::create()));
       // $this->twig->addGlobal("head", $this->getHeadTemplate($this->twig));
        $this->twig->addGlobal("templateList", $this->fileList);
        $this->twig->addGlobal("useragent", $this->getBrowserClasses());
        $this->twig->addGlobal('_ynfinite', $this->data);
        $this->twig->addGlobal("_templates", $this->templates);
        $this->twig->addGlobal("urlData", $this->getURIData());



        $_yfunc = new \Twig\TwigFunction('_yfunc', function($methode){

            $func = new TwigUtils($this->twig, $this->data, $this->fileList);

            $arg_list = func_get_args();
            unset($arg_list[0]);

            return call_user_func_array( array($func, $methode) , $arg_list );

        }, ['is_safe' => ['html']]);

        $this->twig->addFunction($_yfunc);

        return $this->twig->render($this->fileList['index'], $this->data);
    }

    public function renderToolbar(){

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


    public function getBrowserClasses() {
        $provider = new WhichBrowser();
        $uaArray = array();
        try {

            $useragent = $provider->parse($_SERVER['HTTP_USER_AGENT']);

            $uaArray = array(
                $useragent->getBrowser()->getName(),
                $useragent->getBrowser()->getName()."-".$useragent->getBrowser()->getVersion()->getComplete(),
                $useragent->getRenderingEngine()->getName(),
                $useragent->getOperatingSystem()->getName(),
                $useragent->getOperatingSystem()->getName().$useragent->getOperatingSystem()->getVersion()->getComplete(),
                $useragent->getDevice()->getType(),
            );

            if($useragent->getDevice()->getModel()) {
                $uaArray[] = $useragent->getDevice()->getModel();

            }
            if($useragent->getDevice()->getBrand()) {
                $uaArray[] = $useragent->getDevice()->getBrand();
            }

        } catch (Exception $e){

        }
        return implode(" ", $uaArray);
    }
}