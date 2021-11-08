<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use App\Domain\Request\Service\RequestPageService;
use App\Domain\Request\Service\RenderPageService;
use App\Domain\Request\Service\CacheService;
use SlimSession\Helper as SessionHelper;

use App\Exception\YnfiniteException;

final class RenderPageAction
{
    public function __construct(RequestPageService $requestPageService, RenderPageService $renderPageService, CacheService $cacheService) {
        $this->requestPageService = $requestPageService;
        $this->renderPageService = $renderPageService;
        $this->cacheService = $cacheService;
    }

    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response
    ): ResponseInterface {
        try {
            $data = $this->requestPageService->getPage($request);

            if (is_array($data)) {
                $cacheUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                
                $renderedTemplate = $this->renderPageService->render($data["templates"], $data["data"], $cacheUrl);
                if($data["data"]["page"]["type"] !== "404") {
                    $this->cacheService->createCache("PAGE_".md5($cacheUrl), $renderedTemplate);
                }
                
                $response->getBody()->write($renderedTemplate);
            } else {
                $response->getBody()->write($data);
            }
        }
        catch (YnfiniteException $e) {
            return $this->handleException($e, $response);
        }
        
        return $response;
    }

    private function handleException($e, $response) {
        $error = [];
        $error["message"] = $e->getMessage();
        $error["code"] = $e->getCode();
        $error["trace"] = $e->getTraceAsString();

        $response = $response->withStatus($error["code"]);

        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        switch ($e->getRenderType()) {
            case "error":
                $response->withHeader('Content-Type', 'text/html');
                return $response;
                break;
            case "template":
                if ($e->getRedirect() != null) {
                    return $response->withRedirect($e->getRedirect(), 301);
                }

                $renderedTemplate = $this->renderPageService->render($e->getTemplates(), $e->getData(), $baseUrl);
                $response->getBody()->write($renderedTemplate);

                return $response;
                break;
        }
    }
}