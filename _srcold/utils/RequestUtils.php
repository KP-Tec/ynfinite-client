<?php

namespace Ypsolution\YnfinitePhpClient\utils;

use SlimSession\Helper as SessionHelper;
use Exception;
use Psr\Http\Message\ServerRequestInterface;

class RequestUtils
{

    private $requestUrl;
    private $config;
    private $request;
    private $session;
    private $ch;

    private $headers = [];

    public function __construct($config, $request, SessionHelper $session)
    {
        $this->config = $config;
        $this->request = $request;

        $this->session = $session;
        $cookieArray = array();
        foreach ($_COOKIE as $key => $cookie) {
            $cookieArray[] = $key . "=" . $cookie;
        }

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_VERBOSE, 0);
        if ($this->config["ynfinite"]["settings"]["dev"] === 'true') {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_setopt($this->ch, CURLOPT_COOKIE, implode(";", $cookieArray));

        $this->addHeader("ynfinite-api-key", $config["ynfinite"]["settings"]["api_key"]);
        $this->addHeader("ynfinite-service-id", $config["ynfinite"]["settings"]["service_id"]);

        $this->requestUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    }

    private function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    private function execCurl($url, $port)
    {
        $curlHeaders = [];
        foreach ($this->headers as $key => $header) {
            $curlHeaders[] = $key . ":" . $header;
        }

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $curlHeaders);
        curl_setopt($this->ch, CURLOPT_URL, $url);
        if ($this->config["ynfinite"]["settings"]["dev"] === 'true') {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);

        }
        curl_setopt($this->ch, CURLOPT_PORT, $port);

        $output = curl_exec($this->ch);

        $httpcode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $error = curl_error($this->ch);
        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $body = substr($output, $header_size);

        if ($error) {
            throw new YnfiniteException($error, 500);
        }

        if ($httpcode != 200 && $httpcode != 201 && $httpcode != 206) {
            throw new YnfiniteException($body, $httpcode);
        }

        return $body;
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }


    public function isPage($path)
    {
        $pathInfo = pathinfo($path);
        $isPage = !$pathInfo["extension"] || in_array($pathInfo["extension"], $this->config["ynfinite"]["pageTypes"]);

        return $isPage;
    }

    private function buildServiceUrl($service, $path)
    {
        $host = $service['host'];
        $port = (int)$service['port'];

        switch ($path) {
            case 'ynfinite/gdpr/request':
                $controller = $service['gdpr'];
                break;
            case 'ynfinite/gdpr/delete':
                $controller = $service['gdprDelete'];
                break;
            case 'ynfinite/gdpr/info':
                $controller = $service['gdprInfo'];
                break;
            case 'ynfinite/gdpr/update':
                $controller = $service['gdprUpdate'];
                break;
            default:
                $controller = $service['controller'];
                break;
        }

        $uri = $this->request->getUri();

        $protocol = $uri->getScheme();
        $domain = $uri->getHost();
        $queryParams = $uri->getQuery();

        if ($port !== 80 && $port !== 443) {
            $host = $host . ':' . $port;
        }

        $url = $host . $controller;

        $url .= '?slug=/';
        if ($path) $url .= $path;

        if ($protocol) $url .= '&protocol=' . $protocol;
        if ($host) $url .= '&domain=' . $domain;
        if ($queryParams) $url .= '&' . $queryParams;

        $url .= '&lang=' . substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

        return $url;
    }

    public function requestWebsite(ServerRequestInterface $request)
    {

        $path = $request->getUri()->getPath();
        $service = $this->config["ynfinite"]["services"]["frontend"];
        $useFrontendCache = false;
        if(isset($service['use_frontend_cache'])) {
            $useFrontendCache = filter_var($service['use_frontend_cache'], FILTER_VALIDATE_BOOLEAN);
        }
        $isDev = $this->session->get("dev", false);
        if($useFrontendCache && strpos($path, 'gdpr') === false && !$isDev  ) {
            $service['host'] = $service['frontend_cache_url'];
            $service['port'] = $service['frontend_cache_port'];

            try{
                $this->addHeader('x-user-agent', $request->getHeaderLine('User-Agent'));
            }catch (Exception $e){
                error_log($e);
            }


            return $this->requestFromCache($request, $service);
        }

        return $this->request($request, $service);
    }

    public function requestSitemap(ServerRequestInterface $request)
    {
        $service = $this->config["ynfinite"]["services"]["sitemap"];
        return $this->request($request, $service, false);
    }

    public function requestRobotsTxt(ServerRequestInterface $request)
    {
        $service = $this->config["ynfinite"]["services"]["robotsTxt"];
        return $this->request($request, $service, false);
    }

    private function request(ServerRequestInterface $request, $service, $json = true)
    {
        $path = $request->getUri()->getPath();
        $path = trim($path, '/');

        $url = $this->buildServiceUrl($service, $path);

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_PORT, $service["port"]);
        curl_setopt($this->ch, CURLOPT_ENCODING, "gzip");
        if ($this->config["ynfinite"]["settings"]["dev"] === 'true') {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $jsonObj = null;

        $output = $this->execCurl($url, $service["port"]);
        $httpcode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $error = curl_error($this->ch);
        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $body = substr($output, $header_size);
        if ($json) {
            $jsonObj = json_decode($body, true);
            $body = $jsonObj;
        }
        if ($error) {
            throw new YnfiniteException($error, 500);
        }

        if ($httpcode != 200 && $httpcode != 201 && $httpcode != 206) {
            throw new YnfiniteException($body, $httpcode);
        }

        return $body;
    }

    private function requestFromCache(ServerRequestInterface $request, $service)
    {
        $path = $request->getUri()->getPath();
        $path = trim($path, '/');

        $url = $this->buildServiceUrl($service, $path);

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_PORT, $service["port"]);
        curl_setopt($this->ch, CURLOPT_ENCODING, "gzip");
        if ($this->config["ynfinite"]["settings"]["dev"] === 'true') {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $output = $this->execCurl($url, $service["port"]);
        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $body = substr($output, $header_size);
        $httpcode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $error = curl_error($this->ch);
        if ($error) {
            throw new YnfiniteException($error, 500);
        }

        if ($httpcode != 200 && $httpcode != 201 && $httpcode != 206) {
            throw new YnfiniteException($body, $httpcode, true);
        }

        return $body;
    }

    private function encodeUrl($values)
    {
        $result = array();
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->encodeUrl($value);
            } else {
                $result[$key] = urlencode($value);
            }
        }

        return $result;
    }

    public function sendFormData($postData, $path)
    {
        $service = $this->config["ynfinite"]["services"]["form"];
        $url = $this->buildServiceUrl($service, $path);

        $result = $this->encodeUrl($postData);

        $formData = array("formId" => $postData["formId"], "action" => $postData["action"], "formData" => $result);
        $formData = json_encode($formData, JSON_UNESCAPED_UNICODE);

        $this->addHeader("Content-Type", "application/json");
        $this->addHeader("Content-Length", strlen($formData));

        curl_setopt($this->ch, CURLOPT_POST, TRUE);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $formData);
        curl_setopt($this->ch, CURLOPT_ENCODING, "gzip");
        if ($this->config["ynfinite"]["settings"]["dev"] === 'true') {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);

        }

        $output = $this->execCurl($url, $service["port"]);
        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $header = substr($output, 0, $header_size);
        $body = substr($output, $header_size);

        return $body;
    }
}
