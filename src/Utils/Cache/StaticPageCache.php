<?php

namespace App\Utils\Cache;

class StaticPageCache
{
    const BASIC_PATH = "/../tmp/static_pages/";

    private static function createCacheName()
    {
        $additional_keys = '';
        if ($_SERVER['QUERY_STRING']) {

            parse_str($_SERVER['QUERY_STRING'], $parsedQuery);
            $hasOtherParams = false;
            foreach ($parsedQuery as $key => $value) {
                if ($key !== '_y__ynfinitePerPage' && $key !== '_y__ynfinitePage' && $key !== '_y__ynfiniteForm') {
                    $hasOtherParams = true;
                }
                if ($key === '_y__ynfinitePerPage') {
                    $additional_keys .= '_y__ynfinitePerPage_' . $parsedQuery['_y__ynfinitePerPage'];
                }
                if ($key === '_y__ynfinitePage' ) {
                    $additional_keys .= '_y__ynfinitePage_' . $parsedQuery['_y__ynfinitePage'];
                }
                if ('_y__ynfiniteForm') {
                    $additional_keys .= '_y__ynfiniteForm_' . $parsedQuery['_y__ynfiniteForm'];
                }
            }
            if($hasOtherParams){
                $additional_keys = "NOT_CACHED_LISTING_FILTER";
            }
        }

        $url = strtok($_SERVER["REQUEST_URI"], '?');
        $file = str_replace('/', '-', $url);
        $file = str_replace('.html', '', $file);

        if ($file[0] === "-") {
            $file = substr($file, 1);
        }

        if ($file) {
            $cachefile = 'cached-' . $file;
        } else {
            $cachefile = "cached-index";
        }

        if ($_COOKIE["ynfinite-cookies"]) {
            $ynCookie = json_decode($_COOKIE["ynfinite-cookies"]);
            $activeScripts = implode("-", $ynCookie->activeScripts);
            if ($activeScripts) $cachefile .= "-" . $activeScripts;
        }

        $cachefile .= '.html' . $additional_keys;

        return $cachefile;
    }


    public function createStaticPage($content, $pageType)
    {

        if ($pageType === "listing" && $_SERVER['QUERY_STRING']) {
            parse_str($_SERVER['QUERY_STRING'], $parsedQuery);
            $hasOtherParams = false;
            foreach ($parsedQuery as $key => $value) {
                if ($key !== '_y__ynfinitePerPage' && $key !== '_y__ynfinitePage' && $key !== '_y__ynfiniteForm') {
                    $hasOtherParams = true;
                }
            }
            if ($hasOtherParams) {
                return false;
            }
        }
        if ($pageType === "404") {
            return false;
        }
        $filename = StaticPageCache::createCacheName();
        file_put_contents(getcwd() . StaticPageCache::BASIC_PATH . $filename, $content);
        return $filename;
    }


    public static function getCachedPage()
    {
        $filename = StaticPageCache::createCacheName();
        $path = getcwd() . StaticPageCache::BASIC_PATH . $filename;
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return false;
    }

    public static function invalidateCache($filename)
    {
        $path = getcwd() . StaticPageCache::BASIC_PATH . $filename;

        $result = unlink($path);
        return $result;
    }
}
