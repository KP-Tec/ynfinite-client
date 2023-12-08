<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use App\Domain\Request\Repository\RequestCacheRepository;

use SlimSession\Helper as SessionHelper;

final class InvalidateCacheAction
{
    public function __construct(RequestCacheRepository $repository) {
        $this->repository = $repository;
    }

    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response
    ): ResponseInterface {
    

        $entityBody = file_get_contents('php://input');
        $parsedBody = json_decode($entityBody);

        $data = (array)$request->getServerParams();
        $query = array();
        parse_str($data["QUERY_STRING"], $query);

        $result = false;

        $cacheKeys = $parsedBody->cacheKey;
        if($query && $query["cacheKey"]) {
            $cacheKeys = $query["cacheKey"]; 
        }

        if($cacheKeys) {
            $result = $this->repository->invalidateCache($cacheKeys);
        }

        $response->getBody()->write((string)json_encode(array("success" => $result)));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
        
    }
}