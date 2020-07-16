<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use App\Domain\Request\Service\RequestPage;
use SlimSession\Helper as SessionHelper;

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
}