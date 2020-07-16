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

    public function index(Request $request, Response $response, array $args = []): Response {
        $config = $this->preferences->getConfig();

        if($config["ynfinite"]["installPassword"] !== "" && $this->session->loggedIn !== true) {
            return $response->withHeader('Location', '/ynfinite/install/password')->withStatus(302);
        }
        return $this->render($response, 'install_form.twig', $config["ynfinite"]["settings"]);
    }

    public function password(Request $request, Response $response, array $args = []): Response {
        return $this->render($response, 'install_password_form.twig', []);
    }

    public function checkPassword(Request $request, Response $response, array $args = []): Response {
        $config = $this->preferences->getConfig();
        $body = $request->getBody();
        parse_str($body->getContents(), $postData);

        if($config["ynfinite"]["installPassword"] === $postData["password"]) {
            $this->session->loggedIn = true;
            return $response->withHeader('Location', '/ynfinite/install')->withStatus(302);
        }

        return $this->render($response, 'install_password_form.twig', ["error" => "Password missmatch"]);
    }

    public function save(Request $request, Response $response, array $args = []): Response {
        if($this->session->loggedIn === true) {
            $config = $this->preferences->getConfig();
            $body = $request->getBody();
            parse_str($body->getContents(), $postData);

            $configFile = file_get_contents('./yn-config.php');

            switch ($postData['cache']){
                case "memcache":
                    if(!isset($postData['cache_host']) || $postData['cache_host'] === ''){
                        $cacheHost = '127.0.0.1';
                    } else {
                        $cacheHost = $postData['cache_host'];
                    }

                    if(!isset($postData['cache_port']) || $postData['cache_port'] === ''){
                        $cachePort = '11211';
                    }else{
                        $cachePort =$postData['cache_port'];
                    }
                $configFile = InstallationUtils::replaceValue('YN_CACHE_HOST', $cacheHost, $configFile);
                $configFile = InstallationUtils::replaceValue('YN_CACHE_PORT', $cachePort, $configFile);
                break;
                case "file":
                    $configFile = InstallationUtils::replaceValue('YN_CACHE_HOST', '', $configFile);
                    $configFile = InstallationUtils::replaceValue('YN_CACHE_PORT', '', $configFile);
                break;
            }

            $configFile = InstallationUtils::replaceValue('YN_API_KEY', $postData['api_key'], $configFile);
            $configFile = InstallationUtils::replaceValue('YN_SERVICE_ID',  $postData['service_id'], $configFile);

            if($this->session->loggedIn !== true || $config["ynfinite"]["installPassword"] == "") {
                $password =  InstallationUtils::generateRandomString();
                $configFile = InstallationUtils::replaceValue('YN_INSTALL_PASSWORD',  $password, $configFile);
            }

            file_put_contents('./yn-config.php', $configFile);
            $this->session->loggedIn = true;

            return $response->withHeader('Location', '/ynfinite/install/success')->withStatus(302);
        }
        else {
            return $response->withHeader('Location', '/ynfinite/install/password')->withStatus(302);
        }
    }

    public function success(Request $request, Response $response, array $args = []): Response {
        if($this->session->loggedIn === true) {
            $config = $this->preferences->getConfig();
            return $this->render($response, 'install_success.twig', ["password" => $config["ynfinite"]["installPassword"]]);
        }
        return $response->withHeader('Location', '/ynfinite/install/password')->withStatus(302);
    }


        /*
        if (YN_SERVICE_SERVICE_ID === '') {
            $install = new \Ypsolution\YnfinitePhpClient\install();
            if (YN_INSTALL_PASSWORD == '' || (isset($_POST['password']) && $_POST['password'] == YN_INSTALL_PASSWORD && YN_INSTALL_PASSWORD != '')) {
                return $install->showForm();
            } else {
                $password = null;
                if(isset($_POST['password'])){
                    $password = $_POST['password'];
                }
                return $install->showPasswordForm($password);
            }
        }
        */

    /*
    public function post($request) {
        $body = $request->getBody();

        $install = new \Ypsolution\YnfinitePhpClient\install();
        return $install->sendForm($body);
    }
    */
}