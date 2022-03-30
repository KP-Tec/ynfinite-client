<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Container\ContainerInterface;
use App\Domain\Request\Service\SendFormService;
use App\Domain\Request\Service\RenderPageService;
use App\Domain\Request\Service\CacheService;

use App\Utils\Cache\StaticCache;

use SlimSession\Helper as SessionHelper;

final class SendFormAction
{
    public function __construct(SendFormService $sendFormService, RenderPageService $renderPageService, CacheService $cacheService) {
        $this->sendFormService = $sendFormService;
        $this->renderPageService = $renderPageService;
        $this->cacheService = $cacheService;
    }

    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response
    ): ResponseInterface {
        $formResponse = $this->sendFormService->sendForm($request, $_POST);
        
        if($formResponse) {
            if($formResponse["activeEvent"]["asyncTemplate"]) {
                $rendered = $this->renderPageService->renderTemplate($formResponse["templates"], $formResponse, $formResponse["activeEvent"]["asyncTemplate"]);
    
                $formResponse["rendered"] = $rendered;
            }
    
            if($formResponse["form"]["method"] !== "post") {
                $this->cacheService->createCache("REQUEST", json_encode($formResponse));
            }
        }

        // Build the HTTP response
        $response->getBody()->write((string)json_encode(array(
            "rendered" => $rendered, 
            "listingForm" => $formResponse["listingForm"], 
            "pagination" => $formResponse["pagination"]
        )));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}