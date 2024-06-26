<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use App\Domain\Request\Repository\RequestCacheRepository;

use SlimSession\Helper as SessionHelper;

final class InvalidateAllCacheAction
{

    public $repository;

    public function __construct(RequestCacheRepository $repository) {
        $this->repository = $repository;
    }

    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response
    ): ResponseInterface {

        $result = $this->repository->invalidateAllCache();
 
        $response->getBody()->write((string)json_encode(array("success" => $result)));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
        
    }
}