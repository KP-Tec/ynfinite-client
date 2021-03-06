<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use App\Domain\Request\Service\RequestPageService;
use App\Domain\Request\Service\RenderPageService;
use SlimSession\Helper as SessionHelper;

use App\Exception\YnfiniteException;

final class RenderPageAction
{
    public function __construct(RequestPageService $requestPageService, RenderPageService $renderPageService) {
        $this->requestPageService = $requestPageService;
        $this->renderPageService = $renderPageService;
    }

    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response
    ): ResponseInterface {
        try {
            $data = $this->requestPageService->getPage($request);

            if (is_array($data)) {
                $renderedTemplate = $this->renderPageService->render($data["templates"], $data["data"]);
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