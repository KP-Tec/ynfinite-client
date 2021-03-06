<?php

namespace App\Domain\Request\Utils;

use App\Exception\YnfiniteException;

final class CurlHandler
{
    public function __construct($settings, $postRequest = false) {
        $this->settings = $settings;

        $this->uploadFiles = array();

        $cookieArray = array();
        foreach ($_COOKIE as $key => $cookie) {
            $cookieArray[] = $key . "=" . $cookie;
        }

        $this->ch = curl_init();
        $this->path = "";

        if($postRequest) {
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->ch, CURLOPT_HEADER, 1);
            curl_setopt($this->ch, CURLOPT_POST, TRUE);
            // curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
            // curl_setopt($this->ch, CURLOPT_ENCODING, "gzip");
        }
        else {
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->ch, CURLOPT_HEADER, 1);
            curl_setopt($this->ch, CURLOPT_VERBOSE, 0);
        }

        
        if ($this->settings["dev"] === 'true') {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($this->ch, CURLOPT_COOKIE, implode(";", $cookieArray));
    }

    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function addUploadFiles($files) {
        $this->uploadFiles = $files;
    }

    private function buildServiceUrl($service, $path, $uri)
    {
        $host = $service['host'];
        $port = (int)$service['port'];
        $this->path = $path;

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

        $protocol = $uri->getScheme();
        $domain = $uri->getHost();
        $queryParams = $uri->getQuery();

        if ($port !== 80 && $port !== 443) {
            $host = $host . ':' . $port;
        }

        $url = $host . $controller;

        $url .= '?slug=';
        if ($path) $url .= $path;
        else $url .= "/";

        if ($protocol) $url .= '&protocol=' . $protocol;
        if ($host) $url .= '&domain=' . $domain;
        if ($queryParams) $url .= '&' . $queryParams;

        $url .= '&lang=' . substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

        return $url;
    }

    public function setUrl($service, $path, $uri, $dev = false) {

        $url = $this->buildServiceUrl($service, $path, $uri);

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_PORT, $service["port"]);
        // curl_setopt($this->ch, CURLOPT_ENCODING, "gzip");
        if ($dev) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        }

    }

    public function execWithData($formData) {
        $finalData = array();
        if(count($this->uploadFiles) > 0) {
            foreach($this->uploadFiles["tmp_name"] as $key => $value) {
                if($value) {
                    $formData["formData"]["fields"][$key] = $this->uploadFiles["name"][$key];
                    $finalData[$key] = curl_file_create($value, mime_content_type($value));
                }
            }
        }

        $finalData["formData"] = json_encode($formData["formData"], JSON_UNESCAPED_UNICODE);

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $finalData);
       
        $response = $this->exec();
        
        return $response;
    }

    public function exec() {
        $curlHeaders = [];
        foreach ($this->headers as $key => $header) {
            $curlHeaders[] = $key . ":" . $header;
        }

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $curlHeaders);

        $output = curl_exec($this->ch);

        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $body = substr($output, $header_size);
        $httpcode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $error = curl_error($this->ch);

        if ($error) {
            throw new YnfiniteException($error, 500);
        }

        if ($httpcode != 200 && $httpcode != 201 && $httpcode != 206) {
            throw new YnfiniteException($body, $httpcode, true, $this->path);
        }

        return $body;
    }


}