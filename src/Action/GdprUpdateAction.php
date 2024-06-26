<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Container\ContainerInterface;
use App\Domain\Request\Service\GdprUpdateService;
use App\Domain\Request\Service\RenderPageService;
use App\Domain\Request\Service\CacheService;

use App\Utils\Cache\StaticCache;

use SlimSession\Helper as SessionHelper;

final class GdprUpdateAction
{
    public $gdprRequestService;
    public $renderPageService;
    public $cacheService;

    public function __construct(GdprUpdateService $gdprUpdateService, RenderPageService $renderPageService, CacheService $cacheService) {
        $this->gdprUpdateService = $gdprUpdateService;
        $this->renderPageService = $renderPageService;
        $this->cacheService = $cacheService;
    }

    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response
    ): ResponseInterface {
        $formResponse = $this->gdprUpdateService->sendForm($request, $_POST);
        
        // Build the HTTP response
        $response->getBody()->write((string)json_encode(array(
            "rendered" => $formResponse["response"], 
        )));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}