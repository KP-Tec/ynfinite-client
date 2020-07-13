<?php

namespace Ypsolution\YnfinitePhpClient\controller;

use Ypsolution\YnfinitePhpClient\Preferences;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use SlimSession\Helper as SessionHelper;

use Ypsolution\YnfinitePhpClient\utils\RenderUtils;
use Ypsolution\YnfinitePhpClient\utils\RequestUtils;
use Ypsolution\YnfinitePhpClient\utils\YnfiniteException;

class Frontend extends AbstractTwigController
{

    /**
     * @var Preferences
     */
    private $preferences;

    /**
     * Controller constructor.
     *
     * @param Twig $twig
     * @param SessionHelper $session
     */
    public function __construct(Twig $twig, SessionHelper $session, Preferences $preferences)
    {
        parent::__construct($twig, $session);

        $this->preferences = $preferences;
    }

    public function index(Request $request, Response $response, array $args = []): Response
    {
        $this->profile("Starting call");
        $config = $this->preferences->getConfig();

        $requestHelper = new RequestUtils($config, $request, $this->session);

        $path = $args["params"];

        $queryParams = $request->getQueryParams();
        $this->session->set("dev", $queryParams["dev"] === "true");

        $this->profile("All parameters ready");
        if ($requestHelper->isPage($path) === true) {
            try {
                $data = $requestHelper->requestWebsite($request);
                $this->profile("Recieved data");
                if (is_array($data)) {
                    $renderer = new RenderUtils($config, $data["templates"], $data["data"]);
                    $renderedTemplate = $renderer->render();
                    $this->profile("Page is rendered");
                    $isDev = $this->session->get("dev", false);
                    if ($isDev) {
                        $toolbarRenderer = new RenderUtils($config, ['toolbar.twig'], $data["data"]);
                        $renderedToolbar = $toolbarRenderer->renderToolbar();
                        //$response->getBody()->write($renderedTemplate . $renderedToolbar);
                        $renderedTemplate .= $renderedToolbar;
                        $this->profile("Toolbar is rendered");
                    } else {
                        //$response->getBody()->write($renderedTemplate);
                    }
                    $response->getBody()->write($renderedTemplate);
                    $this->profile("Page is written");


                } else {
                    $response->getBody()->write($data);
                }

            } catch (YnfiniteException $e) {
                return $this->handleException($e, $response);
            }
        }

        return $response;
    }

    public function send(Request $request, Response $response, array $args = []): Response
    {
        $config = $this->preferences->getConfig();
        $requestHelper = new RequestUtils($config, $request, $this->session);

        $path = $args["params"];

        /* TODO: For some reason the Request does not contain the post data.. Investigation is needed
        $body = $request->getBody();
        parse_str($body->getContents(), $postData);
        */

        $formResponse = $requestHelper->sendFormData($_POST, $path);

        $response->getBody()->write($formResponse);

        return $response;
    }

    private function handleException(YnfiniteException $e, Response $response)
    {

        $error = [];
        $error["message"] = $e->getMessage();
        $error["error"] = $e->getCode();
        $error["trace"] = $e->getTraceAsString();

        $response = $response->withStatus($error["error"]);

        switch ($e->getRenderType()) {
            case "error":
                $response->withHeader('Content-Type', 'text/html');
                return $this->render($response, 'error.twig', $error);
                break;
            case "template":
                if ($e->getRedirect() != null) {
                    return $response->withRedirect($e->getRedirect(), 301);
                }

                $renderer = new RenderUtils($e->getTemplates(), $e->getData());
                $renderedTemplate = $renderer->render();

                $response->getBody()->write($renderedTemplate);

                return $response;
                break;
            case "cache-pipe":
                $response->getBody()->write($error["message"]);
                return $response;
                break;

        }

    }
}