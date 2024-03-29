<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use App\Domain\Request\Service\RequestPageService;
use App\Domain\Request\Service\RenderPageService;
use App\Domain\Request\Service\CacheService;
use SlimSession\Helper as SessionHelper;

use App\Exception\YnfiniteException;
use Exception;

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
            if(!$this->requestPageService->isValidUrl()){
                http_response_code(404);
                die();
            }

            $formRequest = $request->getParsedBody();
            if($formRequest && $formRequest["method"] == "post" && !isset($formRequest["hasProof"])){
                throw new Exception("The form has no proof that is was sent by a human. Sorry for you inconvenience.". $formRequest["hasProof"]);
            }

            $data = $this->requestPageService->getPage($request);

            if (is_array($data)) {                
                $renderedTemplate = $this->renderPageService->render($data["templates"], $data["data"]);
                if($data["data"]["page"]["type"] !== "404") {
                    $this->cacheService->createCache("PAGE", $renderedTemplate);
                }
                
                $response->getBody()->write($renderedTemplate);
            } else {
                $response->getBody()->write($data);
            }
        }
        catch (YnfiniteException $e) {
            return $this->handleException($e, $response);
        }

        $response = $response->withHeader('Cache-Control', 'max-age=15');

        return $response;
    }

    private function handleException($e, $response) {
        $error = [];
        $error["message"] = $e->getMessage();
        $error["code"] = $e->getCode();
        $error["trace"] = $e->getTraceAsString();

        $response = $response->withStatus($error["code"]);

        switch ($e->getRenderType()) {
            case "error":
                $response->withHeader('Content-Type', 'text/html');
                return $response;
                break;
            case "template":
                if ($e->getRedirect() != null) {
                    return $response->withRedirect($e->getRedirect(), 301);
                }

                $renderedTemplate = $this->renderPageService->render($e->getTemplates(), $e->getData());
                $response->getBody()->write($renderedTemplate);

                return $response;
                break;
        }
    }
}