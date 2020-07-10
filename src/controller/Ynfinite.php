<?php

namespace Ypsolution\YnfinitePhpClient\controller;

use Ypsolution\YnfinitePhpClient\Preferences;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use SlimSession\Helper as SessionHelper;

use Ypsolution\YnfinitePhpClient\utils\RenderUtils;
use Ypsolution\YnfinitePhpClient\utils\RequestUtils;

class Ynfinite extends AbstractTwigController {

    /**
    * @var Preferences
    */
    private $preferences;

    /**
     * Controller constructor.
     *
     * @param Twig          $twig
     * @param SessionHelper $session
     */
    public function __construct(Twig $twig, SessionHelper $session, Preferences $preferences) {
        parent::__construct($twig, $session);
        $this->preferences = $preferences;
    }

    public function index(Request $request, Response $response, array $args = []): Response {
        $config = $this->preferences->getConfig();
        $requestHelper = new RequestUtils($config, $request, $this->session);

        $sitemap = $requestHelper->requestSitemap($request);

        $response = $response->withHeader('Content-Type','application/xml');
        $response->getBody()->write($sitemap);
        return $response;
    }

    public function robotsTxt(Request $request, Response $response, array $args = []): Response {
        $config = $this->preferences->getConfig();
        $requestHelper = new RequestUtils($config, $request, $this->session);

        $robots = $requestHelper->requestRobotsTxt($request);

        $response = $response->withHeader('Content-Type','text/plain');
        $response->getBody()->write($robots);
        return $response;
    }
}