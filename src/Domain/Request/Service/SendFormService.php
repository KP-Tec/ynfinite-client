<?php

namespace App\Domain\Request\Service;

use Psr\Http\Message\ServerRequestInterface;

use Psr\Container\ContainerInterface;
use SlimSession\Helper as SessionHelper;

use App\Domain\Request\Utils\CurlHandler;

final class SendFormService
{

    private $respository;

    public function __construct(SessionHelper $session, ContainerInterface $container) {
        $this->session = $session;

        $this->settings = $container->get("settings")["ynfinite"];

        $cookieArray = array();
        foreach ($_COOKIE as $key => $cookie) {
            $cookieArray[] = $key . "=" . $cookie;
        }

        $this->curlHandler = new CurlHandler($this->settings, true);

        $this->curlHandler->addHeader("ynfinite-api-key", $this->settings["auth"]["api_key"]);
        $this->curlHandler->addHeader("ynfinite-service-id", $this->settings["auth"]["service_id"]);
    }

    public function sendForm(ServerRequestInterface $request, $postData)
    {
        $result = $this->encodeUrl($postData);

        $formData = array("formId" => $postData["formId"], "action" => $postData["action"], "formData" => $result);


        if($_FILES["fields"] && is_array($_FILES["fields"]["tmp_name"])) {
            $this->curlHandler->addUploadFiles($_FILES["fields"]);
        }

        $service = $this->settings["services"]["form"];

        $uri = $request->getUri();

        $response = $this->request($service, $uri, $formData);

        return $response;
    }

    private function request($service, $uri, $formData)
    {
        $path = $uri->getPath();

        $this->curlHandler->setUrl($service, $path, $uri, filter_var($this->settings["dev"], FILTER_VALIDATE_BOOLEAN));
        $response = $this->curlHandler->execWithData($formData);

        $response = json_decode($response, true);

        return $response;
    }

    private function encodeUrl($values)
    {
        $result = array();
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->encodeUrl($value);
            } else {
                $result[$key] = rawurlencode($value);
            }
        }

        return $result;
    }
}