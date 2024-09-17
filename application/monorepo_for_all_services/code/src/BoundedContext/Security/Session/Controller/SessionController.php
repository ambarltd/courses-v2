<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Controller;

use Galeas\Api\BoundedContext\Security\Session\Command\RefreshToken;
use Galeas\Api\BoundedContext\Security\Session\Command\SignIn;
use Galeas\Api\BoundedContext\Security\Session\Command\SignOut;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\RefreshTokenHandler;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn\SignInHandler;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut\SignOutHandler;
use Galeas\Api\Common\Controller\BaseController;
use Galeas\Api\Common\Controller\RequestSchema;
use Galeas\Api\Common\Controller\ResponseSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class SessionController extends BaseController
{
    public function __construct(
        SignInHandler $signInHandler,
        RefreshTokenHandler $refreshTokenHandler,
        SignOutHandler $signOutHandler
    ) {
        parent::__construct(
            [
                $signInHandler,
                $refreshTokenHandler,
                $signOutHandler,
            ]
        );
    }

    /**
     * @RequestSchema(name="V1_Security_Session_SignIn")
     * @ResponseSchema(name="V1_Security_Session_SignIn")
     */
    #[Route('/security/session/sign-in', name: 'V1_Security_Session_SignIn', methods: ['POST'])]
    public function signIn(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Security_Session_SignIn.json',
            'Response/V1_Security_Session_SignIn.json',
            SignIn::class,
            $this->getService(SignInHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @RequestSchema(name="V1_Security_Session_RefreshToken")
     * @ResponseSchema(name="V1_Security_Session_RefreshToken")
     */
    #[Route('/security/session/refresh-token', name: 'V1_Security_Session_RefreshToken', methods: ['POST'])]
    public function refreshToken(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Security_Session_RefreshToken.json',
            'Response/V1_Security_Session_RefreshToken.json',
            RefreshToken::class,
            $this->getService(RefreshTokenHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @RequestSchema(name="V1_Security_Session_SignOut")
     * @ResponseSchema(name="V1_Security_Session_SignOut")
     */
    #[Route('/security/session/sign-out', name: 'V1_Security_Session_SignOut', methods: ['POST'])]
    public function signOut(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Security_Session_SignOut.json',
            'Response/V1_Security_Session_SignOut.json',
            SignOut::class,
            $this->getService(SignOutHandler::class),
            null,
            Response::HTTP_OK
        );
    }
}
