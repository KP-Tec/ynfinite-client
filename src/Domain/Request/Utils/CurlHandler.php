<?php

namespace App\Domain\Request\Utils;

use App\Exception\YnfiniteException;
use Curl;

final class CurlHandler
{
    public function __construct($settings) {
        $this->settings = $settings;

        $this->uploadFiles = array();

        
        $this->ch = new Curl\Curl();
        $this->path = "";

        $cookieArray = array();
        foreach ($_COOKIE as $key => $cookie) {
            $this->ch->setCookie($key, $cookie);
        }


        if ($this->settings["dev"] === 'true') {
            $this->ch->setOpt(CURLOPT_SSL_VERIFYPEER, false);
            $this->ch->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        }
    
    }

    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function addUploadFiles($files) {
        $this->uploadFiles = $files;
    }

    private function buildServiceUrl($service)
    {
        $host = $service['host'];
        $port = (int)$service['port'];

        $controller = $service['controller'];

        // $protocol = $uri->getScheme();
        // $domain = $uri->getHost();
        // $queryParams = $uri->getQuery();

        if ($port !== 80 && $port !== 443) {
            $host = $host . ':' . $port;
        }

        $url = $host . $controller;
        return $url;
    }

    public function setUrl($service, $path) {

        $url = $this->buildServiceUrl($service);
        $this->path = $path;
        $this->url = $url;
        
        // $this->ch->setOpt(CURLOPT_URL, $url);
        $this->ch->setOpt(CURLOPT_PORT, $service["port"]);
        
    }

    public function exec($postBody) {       
        // $this->ch->setHeader("Content-Type", "multipart/form-data");
        foreach ($this->headers as $key => $header) {
            $this->ch->setHeader($key,$header);
        }

        $this->ch->setOpt(CURLOPT_HEADER, 1);
        $this->ch->setOpt(CURLINFO_HEADER_OUT, true);        
        
        $this->ch->post($this->url, $postBody);

        $response = $this->ch->getResponse();
        $header_size = curl_getinfo($this->ch->curl, CURLINFO_HEADER_SIZE);
        $body = substr($response, $header_size);
        $httpcode = $this->ch->getHttpStatus();
        
        if ($this->ch->error) {
            throw new YnfiniteException($body, $httpcode);
        }

        if ($httpcode != 200 && $httpcode != 201 && $httpcode != 206) {
            throw new YnfiniteException($body, $httpcode, true, $this->path);
        }

        return $body;
    }


}