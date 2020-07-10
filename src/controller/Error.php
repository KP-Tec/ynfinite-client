<?php

namespace Ypsolution\YnfinitePhpClient\controller;

use Ypsolution\YnfinitePhpClient\Preferences;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use SlimSession\Helper as SessionHelper;

use Ypsolution\YnfinitePhpClient\utils\InstallationUtils;

class Install extends AbstractTwigController{

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
    public function __construct(Twig $twig, SessionHelper $session, Preferences $preferences)
    {
        parent::__construct($twig, $session);
        $this->preferences = $preferences;
    }

    public function error(Request $request, Response $response, array $args = []): Response {

        $errorCode = $args['error'];
        $errorMsg = $args['message'];

    }

}