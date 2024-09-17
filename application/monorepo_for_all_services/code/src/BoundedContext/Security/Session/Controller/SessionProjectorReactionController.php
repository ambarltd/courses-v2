<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Controller;

use Galeas\Api\BoundedContext\Security\Session\Projection\HashedPassword\HashedPasswordProjector;
use Galeas\Api\BoundedContext\Security\Session\Projection\Session\SessionProjector;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\UserWithEmailProjector;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername\UserWithUsernameProjector;
use Galeas\Api\Common\Controller\ProjectionReactionController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/security/session')]
class SessionProjectorReactionController extends ProjectionReactionController
{
    private HashedPasswordProjector $hashedPasswordProjector;
    private SessionProjector $sessionProjector;
    private UserWithEmailProjector $userWithEmailProjector;
    private UserWithUsernameProjector $userWithUsernameProjector;

    public function __construct(
        HashedPasswordProjector $hashedPasswordProjector,
        SessionProjector $sessionProjector,
        UserWithEmailProjector $userWithEmailProjector,
        UserWithUsernameProjector $userWithUsernameProjector
    ) {
        $this->hashedPasswordProjector = $hashedPasswordProjector;
        $this->sessionProjector = $sessionProjector;
        $this->userWithEmailProjector = $userWithEmailProjector;
        $this->userWithUsernameProjector = $userWithUsernameProjector;
    }

    #[Route('/projection/hashed_password', name: 'projection_hashed_password', methods: ['POST'] )]
    public function hashedPassword(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->hashedPasswordProjector,
            200
        );
    }

    #[Route('/projection/session', name: 'projection_session', methods: ['POST'] )]
    public function session(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->sessionProjector,
            200
        );
    }

    #[Route('/projection/user_with_email', name: 'projection_user_with_email', methods: ['POST'] )]
    public function userWithEmail(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->userWithEmailProjector,
            200
        );
    }

    #[Route('/projection/user_with_username', name: 'projection_user_with_username', methods: ['POST'] )]
    public function userWithUsername(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->userWithUsernameProjector,
            200
        );
    }
}
