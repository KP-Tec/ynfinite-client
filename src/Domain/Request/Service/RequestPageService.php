<?php

namespace App\Domain\Request\Service;

use Psr\Http\Message\ServerRequestInterface;

use Psr\Container\ContainerInterface;
use SlimSession\Helper as SessionHelper;

use App\Domain\Request\Service\RequestService;

final class RequestPageService extends RequestService
{ 
    private $repository;
    public $settings;
    
    public function __construct(SessionHelper $session, ContainerInterface $container) {
        parent::__construct($session, $container);
    }

    public function getPage(ServerRequestInterface $request)
    {
        $jsonResponse = true;
        $path = $request->getUri()->getPath();

        $postBody = $this->getBody($request);

        $request = $this->request(trim($path), $this->settings["services"]["frontend"], $postBody, $jsonResponse);
        $statusCode = $request["statusCode"];
        $body = $request["body"];
        if(in_array($statusCode, [200, 201, 206], true)){
            // Page render
            $body["type"] = 'page';
            return $body;
        }
        else if(in_array($statusCode, [301, 302, 307, 308], true)){
            header("Location: " . $body['url']);
            die();
        } else if ($statusCode === 404){
            // 404 render
            $body = $body['message'];
            $body["type"] = '404';
            return $body;
        } else {
            // Error
            return array("type" => 'error', 'message' => $body["message"]);
        }
    }

    public function isValidUrl(){
        $allowedFileTypes = ['html', 'htm'];
        $currentURL = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        // Parse URL to get query parameters
        $parsedUrl = parse_url($currentURL);
        $path_parts = pathinfo($parsedUrl['path']);
        $queryString = isset($parsedUrl['query']) ? html_entity_decode($parsedUrl['query']) : '';

        $disallowedParams = ['_y__ynfinitePerPage', '_y__ynfinitePage', '_y_perPage', '_y_page'];
        parse_str($queryString, $queryArray);
        if (array_intersect_key(array_flip($disallowedParams), $queryArray)) {
            return false;
        }

        $queryParams = explode(';', $queryString);
        if ($queryParams && count($queryParams) !== count(array_unique($queryParams))) {
            return false;
        }

        // Parse query string manually and check for duplicate parameters
        $parameters = array_count_values(array_map(function ($pair) {
            return explode('=', $pair)[0];
        }, explode('&', $queryString)));

        return !(max($parameters) > 1) && (!array_key_exists('extension', $path_parts) || (array_key_exists('extension', $path_parts) && in_array($path_parts['extension'], $allowedFileTypes)));
    }
}