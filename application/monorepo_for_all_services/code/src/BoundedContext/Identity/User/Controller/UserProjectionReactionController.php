<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Controller;

use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\PrimaryEmailVerificationCodeProjector;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenEmail\TakenEmailProjector;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\TakenUsernameProjector;
use Galeas\Api\BoundedContext\Identity\User\Reaction\SendPrimaryEmailVerification\SendPrimaryEmailVerificationReactor;
use Galeas\Api\Common\Controller\ProjectionReactionController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/identity/user')]
class UserProjectionReactionController extends ProjectionReactionController
{
    private PrimaryEmailVerificationCodeProjector$primaryEmailVerificationCodeProjector;
    private TakenEmailProjector $takenEmailProjector;
    private TakenUsernameProjector $takenUsernameProjector;
    private SendPrimaryEmailVerificationReactor $sendPrimaryEmailVerificationReactor;

    public function __construct(
        PrimaryEmailVerificationCodeProjector $primaryEmailVerificationCodeProjector,
        TakenEmailProjector $takenEmailProjector,
        TakenUsernameProjector $takenUsernameProjector,
        SendPrimaryEmailVerificationReactor $sendPrimaryEmailVerificationReactor
    ) {
        $this->primaryEmailVerificationCodeProjector = $primaryEmailVerificationCodeProjector;
        $this->takenEmailProjector = $takenEmailProjector;
        $this->takenUsernameProjector = $takenUsernameProjector;
        $this->sendPrimaryEmailVerificationReactor = $sendPrimaryEmailVerificationReactor;
    }

    #[Route('/projection/primary_email_verification_code', name: 'projection_primary_email_verification_code', methods: ['POST'] )]
    public function signUp(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->primaryEmailVerificationCodeProjector,
            200
        );
    }

    #[Route('/projection/taken_email', name: 'projection_taken_email', methods: ['POST'] )]
    public function takenEmail(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->takenEmailProjector,
            200
        );
    }

    #[Route('/projection/taken_username', name: 'projection_taken_username', methods: ['POST'] )]
    public function takenUsername(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->takenUsernameProjector,
            200
        );
    }

    #[Route('/reaction/send_primary_email_verification', name: 'reaction_send_primary_email_verification', methods: ['POST'] )]
    public function sendPrimaryEmailVerificationReactor(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->sendPrimaryEmailVerificationReactor,
            200
        );
    }
}
