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
                if ($key !== '_y_perPage' && $key !== '_y_page' && $key !== '_y__ynfiniteForm') {
                    $hasOtherParams = true;
                }
                if ($key === '_y_perPage') {
                    $additional_keys .= '_y_perPage_' . $parsedQuery['_y_perPage'];
                }
                if ($key === '_y_page' ) {
                    $additional_keys .= '_y_page_' . $parsedQuery['_y_page'];
                }
                if ($key === '_y__ynfiniteForm') {
                    $additional_keys .= '_y__ynfiniteForm_' . $parsedQuery['_y__ynfiniteForm'];
                }
            }
            if($hasOtherParams){
                $additional_keys = "NOT_CACHED_LISTING_FILTER";
            }
        }

        $domain = str_replace('.', '-', $_SERVER["HTTP_HOST"]);

        $url = strtok($_SERVER["REQUEST_URI"], '?');
        $file = str_replace('/', '-', $url);
        $file = strtr($file, array('.html5' => '', '.html' => '', '.htm' => ''));

        if ($file[0] === "-") {
            $file = substr($file, 1);
        }

        $cachefile = $domain."-";

        if ($file) {
            $cachefile .= $file;
        } else {
            $cachefile .= "index";
        }

        if ($_COOKIE["ynfinite-cookies"]) {
            $ynCookie = json_decode($_COOKIE["ynfinite-cookies"]);
            $activeScripts = implode("-", $ynCookie->activeScripts);
            if ($activeScripts) $cachefile .= "-" . $activeScripts;
        }

        $cachefile .= $additional_keys;

        return md5($cachefile) . ".html";
    }


    public function createStaticPage($content, $pageType)
    {

        if (!file_exists(getcwd() . StaticPageCache::BASIC_PATH)) {
            mkdir(getcwd() . StaticPageCache::BASIC_PATH, 0777, true);
        }

        if ($pageType === "listing" && $_SERVER['QUERY_STRING']) {
            parse_str($_SERVER['QUERY_STRING'], $parsedQuery);
            $hasOtherParams = false;
            foreach ($parsedQuery as $key => $value) {
                if ($key !== '_y_perPage' && $key !== '_y_page' && $key !== '_y__ynfiniteForm') {
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
