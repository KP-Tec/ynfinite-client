<?php

namespace App\Utils\Cache;

class StaticPageCache {
    const BASIC_PATH = "/../tmp/static_pages/";

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


        return $cachefile;
    }

    public function createStaticPage($content, $pageType) {
        if($pageType !== "listing" && $pageType !== "404") {
            $filename = StaticPageCache::createCacheName();
            file_put_contents(getcwd().StaticPageCache::BASIC_PATH.$filename, $content);
            return $filename;
        }
        return false;
    }


    public static function getCachedPage() {
        $filename = StaticPageCache::createCacheName();
        
        $path = getcwd().StaticPageCache::BASIC_PATH.$filename;
        
        if(file_exists($path)) {
            return file_get_contents($path);
        }
        return false;
    }

    public static function invalidateCache($filename) {
        $path = getcwd().StaticPageCache::BASIC_PATH.$filename;

        $result = unlink($path);
        return $result;    
    }
}
