<?php

namespace App\Domain\Request\Utils;

final class CurlHandler
{
    public function __construct($settings) {
        $this->settings = $settings;

        $cookieArray = array();
        foreach ($_COOKIE as $key => $cookie) {
            $cookieArray[] = $key . "=" . $cookie;
        }

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_VERBOSE, 0);
        if ($this->settings["dev"] === 'true') {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($this->ch, CURLOPT_COOKIE, implode(";", $cookieArray));
    }

    private function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
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

    public function setUrl($service, $path) {

        $url = $this->buildServiceUrl($service, $path);

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_PORT, $service["port"]);
        curl_setopt($this->ch, CURLOPT_ENCODING, "gzip");
        if ($dev) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        }

    }

    public function exec() {
        $curlHeaders = [];
        foreach ($this->headers as $key => $header) {
            $curlHeaders[] = $key . ":" . $header;
        }

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $curlHeaders);

        $this->output = curl_exec($this->ch);
    }


}