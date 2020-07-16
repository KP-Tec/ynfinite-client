<?php

namespace App\Domain\Request\Service;

use Psr\Container\ContainerInterface;
use SlimSession\Helper as SessionHelper;
use App\Domain\Request\Repository\RequestCacheRepository;

use App\Domain\Request\Utils\CurlHandler;

final class RequestUtils
{

    private $respository;

    public function __construct(RequestCacheRepository $respository, SessionHelper $session, ContainerInterface $container) {
        $this->repository = $repository;
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

    public function getPage(ServerRequestInterface $request)
    {
        $jsonResponse = true;
        $path = $request->getUri()->getPath();

        $queryParams = $request->getQueryParams();
        $this->session->set("dev", $queryParams["dev"] === "true");

        $service = $this->settings["services"]["frontend"];
        
        $useFrontendCache = filter_var($service['use_frontend_cache'], FILTER_VALIDATE_BOOLEAN);

        $isDev = $this->session->get("dev", false);

        if($useFrontendCache && strpos($path, 'gdpr') === false && !$isDev  ) {
            $service = $this->settings["services"]["frontend-cache"];

            try{
                $this->curlHandler->addHeader('x-user-agent', $request->getHeaderLine('User-Agent'));
            } catch (Exception $e){
                error_log($e);
            }
            $jsonResponse = false;
        }

        return $this->request(trim($path, '/'), $service, $jsonResponse);
    }

    private function request($path, $service, $json = true)
    {
        $this->curlHandler->setUrl($service, $path, filter_var($this->settings["dev"], FILTER_VALIDATE_BOOLEAN));
        $response = $this->execCurl($url, $service["port"]);
        
        if ($json) {
            $response = json_decode($response, true);
        }
    
        return $response;
    }

    private function requestFromCache($path, $service, $json = true) {

    }

}