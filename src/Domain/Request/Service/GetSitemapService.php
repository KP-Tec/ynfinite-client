<?php

namespace App\Domain\Request\Service;

use Psr\Http\Message\ServerRequestInterface;

use Psr\Container\ContainerInterface;
use SlimSession\Helper as SessionHelper;

use App\Domain\Request\Service\RequestService;

final class GetSitemapService extends RequestService
{
    private $repository;
    public $settings;

    public function __construct(SessionHelper $session, ContainerInterface $container) {
        parent::__construct($session, $container);    
    }

    public function getSitemap(ServerRequestInterface $request)
    {
        $jsonResponse = true;
        $path = $request->getUri()->getPath();

        $postBody = $this->getBody($request);
        $result = $this->request(trim($path), $this->settings["services"]["sitemap"], $postBody, $jsonResponse);

        return $result["body"];
    }
}