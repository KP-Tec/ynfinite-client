<?php

namespace Ypsolution\YnfinitePhpClient\controller;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use SlimSession\Helper as SessionHelper;

abstract class AbstractTwigController extends AbstractController
{
    /**
     * @var Twig
     */
    protected $twig;

    /**
     * @var SessionHelper
     */
    protected $session;


    /**
     * AbstractController constructor.
     *
     * @param Twig $twig
     * @param Sessionhelper $session
     */
    public function __construct(Twig $twig, SessionHelper $session)
    {
        $this->twig = $twig;
        $this->session = $session;
    }

    /**
     * Render the template and write it to the response.
     *
     * @param Response $response
     * @param string $template
     * @param array $renderData
     *
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function render(Response $response, string $template, array $renderData = []): Response
    {
        return $this->twig->render($response, $template, $renderData);
    }
}