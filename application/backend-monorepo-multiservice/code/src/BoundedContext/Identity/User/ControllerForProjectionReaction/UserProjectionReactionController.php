<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\ControllerForProjectionReaction;

use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\PrimaryEmailVerificationCodeProjector;
use Galeas\Api\BoundedContext\Identity\User\Projection\SentVerificationEmailV2\SentVerificationEmailProjector;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenEmail\TakenEmailProjector;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\TakenUsernameProjector;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetailsV2\UserDetailsProjector;
use Galeas\Api\BoundedContext\Identity\User\Reaction\SendPrimaryEmailVerification\SendPrimaryEmailVerificationReactor;
use Galeas\Api\CommonController\ProjectionReactionController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/identity/user')]
class UserProjectionReactionController extends ProjectionReactionController
{
    private PrimaryEmailVerificationCodeProjector $primaryEmailVerificationCodeProjector;
    private TakenEmailProjector $takenEmailProjector;
    private TakenUsernameProjector $takenUsernameProjector;
    private SentVerificationEmailProjector $sentVerificationEmailProjector;
    private SendPrimaryEmailVerificationReactor $sendPrimaryEmailVerificationReactor;
    private UserDetailsProjector $userDetailsProjector;

    public function __construct(
        PrimaryEmailVerificationCodeProjector $primaryEmailVerificationCodeProjector,
        TakenEmailProjector $takenEmailProjector,
        TakenUsernameProjector $takenUsernameProjector,
        SentVerificationEmailProjector $sentVerificationEmailProjector,
        SendPrimaryEmailVerificationReactor $sendPrimaryEmailVerificationReactor,
        UserDetailsProjector $userDetailsProjector
    ) {
        $this->primaryEmailVerificationCodeProjector = $primaryEmailVerificationCodeProjector;
        $this->takenEmailProjector = $takenEmailProjector;
        $this->takenUsernameProjector = $takenUsernameProjector;
        $this->sentVerificationEmailProjector = $sentVerificationEmailProjector;
        $this->sendPrimaryEmailVerificationReactor = $sendPrimaryEmailVerificationReactor;
        $this->userDetailsProjector = $userDetailsProjector;
    }

    #[Route('/projection/primary_email_verification_code', name: 'projection_primary_email_verification_code', methods: ['POST'])]
    public function signUp(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->primaryEmailVerificationCodeProjector,
            200
        );
    }

    #[Route('/projection/taken_email', name: 'projection_taken_email', methods: ['POST'])]
    public function takenEmail(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->takenEmailProjector,
            200
        );
    }

    #[Route('/projection/taken_username', name: 'projection_taken_username', methods: ['POST'])]
    public function takenUsername(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->takenUsernameProjector,
            200
        );
    }

    #[Route('/projection/sent_verification_email_v2', name: 'projection_sent_verification_email_v2', methods: ['POST'])]
    public function sentVerificationEmail(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->sentVerificationEmailProjector,
            200
        );
    }

    #[Route('/projection/user_details_v2', name: 'projection_user_details_v2', methods: ['POST'])]
    public function userDetails(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->userDetailsProjector,
            200
        );
    }

    #[Route('/reaction/send_primary_email_verification', name: 'reaction_send_primary_email_verification', methods: ['POST'])]
    public function sendPrimaryEmailVerificationReactor(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->sendPrimaryEmailVerificationReactor,
            200
        );
    }
}
