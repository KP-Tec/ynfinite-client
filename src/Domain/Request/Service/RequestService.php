<?php

namespace App\Domain\Request\Service;

use Exception;
use SlimSession\Helper as SessionHelper;
use Psr\Container\ContainerInterface;

use App\Domain\Request\Utils\CurlHandler;

class RequestService {

    public function __construct(SessionHelper $session, ContainerInterface $container) {
        $this->session = $session;

        $this->settings = $container->get("settings")["ynfinite"];

        $cookieArray = array();
        foreach ($_COOKIE as $key => $cookie) {
            $cookieArray[] = $key . "=" . $cookie;
        }

        $this->curlHandler = new CurlHandler($this->settings);

        $this->curlHandler->addHeader("ynfinite-api-key", $this->settings["auth"]["api_key"]);
        $this->curlHandler->addHeader("ynfinite-service-id", $this->settings["auth"]["service_id"]);
    }

    protected function getFiles($request) {
        $uploadFields = $request->getUploadedFiles();

        $files = array();

        foreach ($uploadFields["fields"] ?? [] as $key => $file) {

            if (is_array($file)) {
                foreach ($file as $i => $part) {
                    if($part->getFilePath()) {
                        $files["files.".$key.'['.$i.']'] = curl_file_create($part->getFilePath(), $part->getClientMediaType(), $part->getClientFilename());
                    }
                    
                }
            } else {
                if($file->getFilePath()) {
                    $files["files.".$key] = curl_file_create($file->getFilePath(), $file->getClientMediaType(), $file->getClientFilename());
                }
            }
        }        

        return $files;
    }

    protected function checkPostProof($request) {
        $body = $request->getParsedBody();
        if(!$body) {
            $body = json_decode(file_get_contents('php://input'));
        }

        if(!$body["hasProof"] || !$body["proofenHash"]) {
            throw new Exception("The form has no proof that is was sent by a human. Sorry for you inconvenience.");
        }

        return true;
    }

    protected function getBody($request) {

        $files = $this->getFiles($request);
        
        $body = $request->getParsedBody();
        if(!$body) {
            $body = json_decode(file_get_contents('php://input'));
        }

        $url = $request->getUri()->getScheme()."://".$request->getUri()->getHost().$request->getUri()->getPath();
        if($request->getUri()->getQuery()) {
            $url .= "?".$request->getUri()->getQuery();
        }

        $postBody = array_merge(array(
            "method" => $request->getMethod(),
            "url" => $url,
            "session" => json_encode($_SESSION),
            "referer" => $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $url
        ), $files);

        if($body) {
            $postBody["body"] = json_encode($body);
        }

        return $postBody;
    }

    protected function request($path, $service, $body = array(), $json = true)
    {
        $this->curlHandler->setUrl($service, $path);
    
        $response = $this->curlHandler->exec($body);    

        if ($json) {
            $response = json_decode($response, true);
        }

        return $response;
    }
}