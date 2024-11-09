<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\AuthenticationAllServices\ControllerForProjectionReaction;

use Galeas\Api\BoundedContext\AuthenticationAllServices\Projection\Session\SessionProjector;
use Galeas\Api\CommonController\ProjectionReactionController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/authentication_all_services')]
class SessionProjectionReactionController extends ProjectionReactionController
{
    private SessionProjector $sessionProjector;

    public function __construct(SessionProjector $sessionProjector)
    {
        $this->sessionProjector = $sessionProjector;
    }

    #[Route('/projection/session', name: 'authentication_all_services_session', methods: ['POST'])]
    public function hashedPassword(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->sessionProjector,
            200
        );
    }
}
