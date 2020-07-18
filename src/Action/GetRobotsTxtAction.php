<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use App\Domain\Request\Service\GetRobotsTxtService;

use SlimSession\Helper as SessionHelper;

final class GetRobotsTxtAction
{
    public function __construct(GetRobotsTxtService $getRobotsTxtService) {
        $this->getRobotsTxtService = $getRobotsTxtService;
    }

    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response
    ): ResponseInterface {
        
        $sitemap = $this->getRobotsTxtService->getRobotsTxt($request);
    
        // Build the HTTP response
        $response->getBody()->write($sitemap);

        return $response
            ->withHeader('Content-Type','text/plain')
            ->withStatus(201);
    }
}