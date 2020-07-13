<?php

namespace Ypsolution\YnfinitePhpClient;


class StaticPageCache {
    private static function createCacheName() {
        $url = $_SERVER["REQUEST_URI"];
        $break = explode('/', $url);
        $file = $break[count($break) - 1];

        if($file) {
            $cachefile = 'cached-'.substr_replace($file ,"", -4).'html';
        }
        else {
            $cachefile = "cached-index.html";
        }


        return getcwd()."/staticPages/".$cachefile;
    }

    public function createStaticPage($content, $pageType) {
        if($pageType !== "404") {
            $filename = StaticPageCache::createCacheName();
            file_put_contents($filename, $content);
        }
    }


    public static function getCachedPage() {
        $filename = StaticPageCache::createCacheName();
        error_log("TRYING TO READT ".$filename);
        if(file_exists($filename)) {
            error_log("FILE EXISTS");
            return file_get_contents($filename);
        }
        return false;
    }
}
