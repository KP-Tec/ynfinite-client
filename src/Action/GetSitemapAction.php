<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use App\Domain\Request\Service\GetSitemapService;

use SlimSession\Helper as SessionHelper;

final class GetSitemapAction
{
    public function __construct(GetSitemapService $getSitemapService) {
        $this->getSitemapService = $getSitemapService;
    }

    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response
    ): ResponseInterface {
        
        $sitemap = $this->getSitemapService->getSitemap($request);
    
        // Build the HTTP response
        $response->getBody()->write($sitemap);

        return $response
            ->withHeader('Content-Type','application/xml')
            ->withStatus(201);
    }
}