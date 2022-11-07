<?php

namespace App\Utils\Cache;

class StaticCache
{
    const BASIC_PATH = "/../tmp/static_pages/";

    public static function createCacheKey($type, $pageOnly = false) {
        $filename = null;
        switch($type) {
            case "PAGE": 
                $filename = StaticCache::createPageCacheKey($pageOnly);
                break;
            case "REQUEST":
                $filename = StaticCache::createRequestCacheKey($pageOnly);
                break;
        }
        return $filename;
    }

    public static function createPageCacheKey($pageOnly = false)
    {
        $requestUrlParts = explode("?", $_SERVER["REQUEST_URI"]);

        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$requestUrlParts[0]";

        $key = "PAGE_".md5($url);

        if($pageOnly) {
            return $key;
        }

        if(sizeof($requestUrlParts) > 1 && $requestUrlParts[1]) {
            $key .= "_".md5($requestUrlParts[1]);
        }
    
        if ($_COOKIE["ynfinite-cookies"] ?? false) {
            $ynCookie = json_decode($_COOKIE["ynfinite-cookies"]);
            $activeScripts = implode("-", $ynCookie->activeScripts);
            if ($activeScripts) $key .= "_" . md5($activeScripts);
        }

        return $key;
    }

    public static function createRequestCacheKey($pageOnly = false)
    {
        $requestUrlParts = explode("?", $_SERVER["HTTP_REFERER"]);

        $url = $requestUrlParts[0];

        $key = "REQUEST_".md5($url);

        if($pageOnly) {
            return $key;
        }

        if($requestUrlParts[1]) {
            $key .= "_".md5($requestUrlParts[1]);
        }
    
        if ($_COOKIE["ynfinite-cookies"]) {
            $ynCookie = json_decode($_COOKIE["ynfinite-cookies"]);
            $activeScripts = implode("-", $ynCookie->activeScripts);
            if ($activeScripts) $key .= "_" . md5($activeScripts);
        }

        return $key;
    }



    public static function createCache($type, $content)
    {
        if (!file_exists(getcwd() . StaticCache::BASIC_PATH)) {
            mkdir(getcwd() . StaticCache::BASIC_PATH, 0777, true);
        }

        $filename = StaticCache::createCacheKey($type);
        
        file_put_contents(getcwd() . StaticCache::BASIC_PATH . $filename. ".cache", $content);
        return $filename;
    }


    public static function getCache($type)
    {
        $filename = StaticCache::createCacheKey($type);

        $path = getcwd() . StaticCache::BASIC_PATH . $filename . ".cache";
        if(file_exists($path)) {
            return file_get_contents($path);
        }
        return false;
    }

    public static function invalidateCache($filename)
    {
        $path = getcwd() . StaticCache::BASIC_PATH . $filename . ".cache";

        $result = false;
        if(file_exists($path)) {
            $result = unlink($path);
        }
        
        return $result;
    }
}
