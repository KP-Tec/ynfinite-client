<?php

namespace App\Utils\Cache;

class StaticCache
{
    const BASIC_PATH = "/../tmp/static_cache/";

    private static function createCacheName($cachefile)
    {
        if ($_COOKIE["ynfinite-cookies"]) {
            $ynCookie = json_decode($_COOKIE["ynfinite-cookies"]);
            $activeScripts = implode("-", $ynCookie->activeScripts);
            if ($activeScripts) $cachefile .= "_" . md5($activeScripts);
        }

        return $cachefile . ".cache";
    }


    public function createCache($key, $content)
    {

        if (!file_exists(getcwd() . StaticCache::BASIC_PATH)) {
            mkdir(getcwd() . StaticCache::BASIC_PATH, 0777, true);
        }

        $filename = StaticCache::createCacheName($key);
        file_put_contents(getcwd() . StaticCache::BASIC_PATH . $filename, $content);
        return $filename;
    }


    public static function getCache($key)
    {
        $filename = StaticCache::createCacheName($key);
        $path = getcwd() . StaticCache::BASIC_PATH . $filename;
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return false;
    }

    public static function invalidateCache($filename)
    {
        $path = getcwd() . StaticCache::BASIC_PATH . $filename;

        $result = unlink($path);
        return $result;
    }
}
