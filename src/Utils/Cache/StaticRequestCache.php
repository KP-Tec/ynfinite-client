<?php

namespace App\Utils\Cache;

class StaticRequestCache
{
    const BASIC_PATH = "/../tmp/static_pages/";

    private static function createCacheName()
    {
        $url = $_SERVER['HTTP_REFERER'];
        return md5($url) . ".json";
    }


    public function createCache($content)
    {
        if (!file_exists(getcwd() . StaticRequestCache::BASIC_PATH)) {
            mkdir(getcwd() . StaticRequestCache::BASIC_PATH, 0777, true);
        }
     
        $filename = StaticRequestCache::createCacheName();
        file_put_contents(getcwd() . StaticRequestCache::BASIC_PATH . $filename, json_encode($content));
        return $filename;
    }


    public static function getCache()
    {
        $filename = StaticRequestCache::createCacheName();
        $path = getcwd() . StaticRequestCache::BASIC_PATH . $filename;
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return false;
    }

    public static function invalidateCache($filename)
    {
        $path = getcwd() . StaticRequestCache::BASIC_PATH . $filename;

        $result = unlink($path);
        return $result;
    }
}
