<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use App\Domain\Request\Service\SendFormService;

use SlimSession\Helper as SessionHelper;

final class SendFormAction
{
    public function __construct(SendFormService $sendFormService) {
        $this->sendFormService = $sendFormService;
    }

    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response
    ): ResponseInterface {
        $formResponse = $this->sendFormService->sendForm($request, $_POST);
    
        // Build the HTTP response
        $response->getBody()->write((string)json_encode($formResponse));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }
}