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
    public $requestPageService;
    public $renderPageService;
    public $cacheService;

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
                http_response_code(410);
                die();
            }

            if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == '/logout') {
                if (isset($_COOKIE['leadGroupIds'])) {
                    $expires = time() - 3600;
                    setcookie('leadGroupIds', '', $expires, '/');
                    setcookie('ynfinite-session', '', $expires, '/');
                    unset($_COOKIE['leadGroupIds']);
                    unset($_COOKIE['ynfinite-session']);
                }
                header('Location: /');
                die();
            }
            
            $formRequest = $request->getParsedBody();
            if($formRequest && $formRequest["method"] == "post" && !isset($formRequest["hasProof"])){
                throw new Exception("The form has no proof that is was sent by a human. Sorry for you inconvenience.");
            }
            
            $data = $this->requestPageService->getPage($request);
            
            if (is_array($data)) {
                if (isset($data['data']['leadGroupIds'])) {
                    $leadGroupIds = $data['data']['leadGroupIds'];
                    setcookie('leadGroupIds', $leadGroupIds, time() + (86400 * 30), "/");
                }
                if (isset($_COOKIE["loginToken"])) {
                    $_SESSION["loginToken"] = $_COOKIE["loginToken"];
                }
                if (!isset($data["data"]["errors"]) && isset($_POST['fields'])
                    && isset($_POST['fields']['general_e-mail'])
                    && isset($_POST['fields']['general_password'])) {
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    die();
                }
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