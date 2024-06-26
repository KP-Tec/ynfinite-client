<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Container\ContainerInterface;
use App\Domain\Request\Service\GdprRequestService;
use App\Domain\Request\Service\RenderPageService;
use App\Domain\Request\Service\CacheService;

use App\Utils\Cache\StaticCache;

use SlimSession\Helper as SessionHelper;

final class GdprRequestAction
{
    public $gdprRequestService;
    public $renderPageService;
    public $cacheService;

    public function __construct(GdprRequestService $gdprRequestService, RenderPageService $renderPageService, CacheService $cacheService) {
        $this->gdprRequestService = $gdprRequestService;
        $this->renderPageService = $renderPageService;
        $this->cacheService = $cacheService;
    }

    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response
    ): ResponseInterface {
        
        $formResponse = $this->gdprRequestService->sendForm($request, $_POST);
        
        // Build the HTTP response
        $response->getBody()->write((string)json_encode(array(
            "rendered" => $formResponse["response"], 
        )));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}