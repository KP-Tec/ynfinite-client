<?php

namespace App\Domain\Request\Service;

use Psr\Http\Message\ServerRequestInterface;

use Psr\Container\ContainerInterface;
use SlimSession\Helper as SessionHelper;

use App\Domain\Request\Utils\CurlHandler;
use App\Domain\Request\Service\BaseRequestService;

final class GetSitemapService
{
    public function __construct(SessionHelper $session, ContainerInterface $container) {
        $this->session = $session;

        $this->settings = $container->get("settings")["ynfinite"];

        $this->curlHandler = new CurlHandler($this->settings);

        $this->curlHandler->addHeader("ynfinite-api-key", $this->settings["auth"]["api_key"]);
        $this->curlHandler->addHeader("ynfinite-service-id", $this->settings["auth"]["service_id"]);
    }

    public function getSitemap(ServerRequestInterface $request)
    {

        $service = $this->settings["services"]["sitemap"];

        $uri = $request->getUri();

        $response = $this->request($service, $uri);

        return $response;
    }

    private function request($service, $uri)
    {
        $path = $uri->getPath();

        $this->curlHandler->setUrl($service, trim($path, '/'), $uri, filter_var($this->settings["dev"], FILTER_VALIDATE_BOOLEAN));
        $response = $this->curlHandler->exec();

        return $response;
    }
}