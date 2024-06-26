<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use App\Domain\Request\Service\GetSitemapService;
use App\Domain\Request\Service\RenderSitemapService;

use SlimSession\Helper as SessionHelper;

final class GetSitemapAction
{

    public $getSitemapService;
    public $renderSitemapService;

    public function __construct(GetSitemapService $getSitemapService, RenderSitemapService $renderSitemapService) {
        $this->getSitemapService = $getSitemapService;
        $this->renderSitemapService = $renderSitemapService;
    }

    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response
    ): ResponseInterface {
        
        $sitemap = $this->getSitemapService->getSitemap($request);
        $renderedSitemap = $this->renderSitemapService->render($sitemap);

        // Build the HTTP response
        $response->getBody()->write($renderedSitemap);

        return $response
            ->withHeader('Content-Type','application/xml')
            ->withStatus(200);
    }
}