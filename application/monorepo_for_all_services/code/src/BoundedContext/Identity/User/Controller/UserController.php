<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Controller;

use Galeas\Api\BoundedContext\Identity\User\Command\RequestPrimaryEmailChange;
use Galeas\Api\BoundedContext\Identity\User\Command\SignUp;
use Galeas\Api\BoundedContext\Identity\User\Command\VerifyPrimaryEmail;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\RequestPrimaryEmailChangeHandler;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\SignUpHandler;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\VerifyPrimaryEmailHandler;
use Galeas\Api\Common\Controller\BaseController;
use Galeas\Api\Common\Controller\RequestSchema;
use Galeas\Api\Common\Controller\ResponseSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class UserController extends BaseController
{
    public function __construct(
        SignUpHandler $signUpHandler,
        VerifyPrimaryEmailHandler $verifyPrimaryEmailHandler,
        RequestPrimaryEmailChangeHandler $requestPrimaryEmailChangeHandler
    ) {
        parent::__construct(
            [
                $signUpHandler,
                $verifyPrimaryEmailHandler,
                $requestPrimaryEmailChangeHandler
            ]
        );
    }

    /**
     * @RequestSchema(name="V1_Identity_User_SignUp")
     * @ResponseSchema(name="V1_Identity_User_SignUp")
     */
    #[Route('/identity/user/sign-up', name: 'V1_Identity_User_SignUp', methods: ['POST'] )]
    public function signUp(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Identity_User_SignUp.json',
            'Response/V1_Identity_User_SignUp.json',
            SignUp::class,
            $this->getService(SignUpHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @RequestSchema(name="V1_Identity_User_VerifyPrimaryEmail")
     * @ResponseSchema(name="V1_Identity_User_VerifyPrimaryEmail")
     */
    #[Route('/identity/user/verify-primary-email', name: 'V1_Identity_User_VerifyPrimaryEmail', methods: ['POST'] )]
    public function verifyPrimaryEmail(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Identity_User_VerifyPrimaryEmail.json',
            'Response/V1_Identity_User_VerifyPrimaryEmail.json',
            VerifyPrimaryEmail::class,
            $this->getService(VerifyPrimaryEmailHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @RequestSchema(name="V1_Identity_User_RequestPrimaryEmailChange")
     * @ResponseSchema(name="V1_Identity_User_RequestPrimaryEmailChange")
     */
    #[Route('/identity/user/request-primary-email-change', name: 'V1_Identity_User_RequestPrimaryEmailChange', methods: ['POST'] )]
    public function requestPrimaryEmailChange(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Identity_User_RequestPrimaryEmailChange.json',
            'Response/V1_Identity_User_RequestPrimaryEmailChange.json',
            RequestPrimaryEmailChange::class,
            $this->getService(RequestPrimaryEmailChangeHandler::class),
            null,
            Response::HTTP_OK
        );
    }
}
