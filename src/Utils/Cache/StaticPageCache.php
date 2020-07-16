<?php

namespace App\Utils\Cache;

class StaticPageCache {
    private static function createCacheName() {
        $url = $_SERVER["REQUEST_URI"];
        $break = explode('/', $url);
        $file = $break[count($break) - 1];

        if($file) {
            $cachefile = 'cached-'.substr_replace($file ,"", -5);
        }
        else {
            $cachefile = "cached-index";
        }

        if($_COOKIE["ynfinite-cookies"]) {
            $ynCookie = json_decode($_COOKIE["ynfinite-cookies"]);
            $activeScripts = implode("-", $ynCookie->activeScripts);
            if($activeScripts) $cachefile .= "-".$activeScripts;
        }



        $cachefile .= '.html';


        return getcwd()."/staticPages/".$cachefile;
    }

    public function createStaticPage($content, $pageType) {
        if($pageType !== "listing" && $pageType !== "404") {
            $filename = StaticPageCache::createCacheName();
            file_put_contents($filename, $content);
        }
    }


    public static function getCachedPage() {
        $filename = StaticPageCache::createCacheName();
        if(file_exists($filename)) {
            return file_get_contents($filename);
        }
        return false;
    }
}
