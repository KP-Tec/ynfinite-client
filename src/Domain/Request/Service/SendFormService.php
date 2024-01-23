<?php

namespace App\Domain\Request\Service;

use Psr\Http\Message\ServerRequestInterface;

use Psr\Container\ContainerInterface;
use SlimSession\Helper as SessionHelper;
use App\Domain\Request\Repository\RequestCacheRepository;

use App\Domain\Request\Service\RequestService;

use App\Domain\Request\Utils\CurlHandler;

final class SendFormService extends RequestService
{
    private $respository;

    public function __construct(SessionHelper $session, RequestCacheRepository $repository, ContainerInterface $container) {
        parent::__construct($session, $container);
        $this->repository = $repository;
    }

    public function sendForm(ServerRequestInterface $request, $postData)
    {
        $jsonResponse = true;
        $path = $request->getUri()->getPath();
    
        if($postBody['method'] === "post" && !$this->securityCheck($request)) {
            return $this->securityError;
        }
        
        $postBody = $this->getBody($request);
        $postBody["referer"] = $_SERVER['HTTP_REFERER'];

        $this->checkPostProof($request);

        $response = $this->request(trim($path), $this->settings["services"]["form"], $postBody, $jsonResponse);

        return $response;
    }

    private function securityCheck($request) 
    {
        $body = $request->getParsedBody();
        if($body["confirm_email"] !== "my@email.com") {
            $this->securityError = array(
                "success" => false,
                "rendered" => "<p>This server does no longer exists. Please contact your administrator!"
            );
            
            return false;
        }

        return true;

    }
}