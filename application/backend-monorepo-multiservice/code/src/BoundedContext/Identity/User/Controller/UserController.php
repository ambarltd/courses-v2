<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Controller;

use Galeas\Api\BoundedContext\Identity\User\Command\RequestPrimaryEmailChange;
use Galeas\Api\BoundedContext\Identity\User\Command\SignUp;
use Galeas\Api\BoundedContext\Identity\User\Command\VerifyPrimaryEmail;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\RequestPrimaryEmailChangeHandler;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\SignUpHandler;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\VerifyPrimaryEmailHandler;
use Galeas\Api\BoundedContext\Identity\User\Query\ListSentVerificationEmailQuery;
use Galeas\Api\BoundedContext\Identity\User\QueryHandler\ListSentVerificationEmailQueryHandler;
use Galeas\Api\CommonController\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class UserController extends BaseController
{
    private SignUpHandler $signUpHandler;
    private VerifyPrimaryEmailHandler $verifyPrimaryEmailHandler;
    private RequestPrimaryEmailChangeHandler $requestPrimaryEmailChangeHandler;
    private ListSentVerificationEmailQueryHandler $listSentVerificationEmailQueryHandler;

    public function __construct(
        SignUpHandler $signUpHandler,
        VerifyPrimaryEmailHandler $verifyPrimaryEmailHandler,
        RequestPrimaryEmailChangeHandler $requestPrimaryEmailChangeHandler,
        ListSentVerificationEmailQueryHandler $listSentVerificationEmailQueryHandler
    ) {
        $this->signUpHandler = $signUpHandler;
        $this->verifyPrimaryEmailHandler = $verifyPrimaryEmailHandler;
        $this->requestPrimaryEmailChangeHandler = $requestPrimaryEmailChangeHandler;
        $this->listSentVerificationEmailQueryHandler = $listSentVerificationEmailQueryHandler;
    }

    /**
     * @RequestSchema(name="V1_Identity_User_SignUp")
     *
     * @ResponseSchema(name="V1_Identity_User_SignUp")
     */
    #[Route('/identity/user/sign-up', name: 'V1_Identity_User_SignUp', methods: ['POST'])]
    public function signUp(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Identity_User_SignUp.json',
            'Response/V1_Identity_User_SignUp.json',
            SignUp::class,
            $this->signUpHandler,
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @RequestSchema(name="V1_Identity_User_VerifyPrimaryEmail")
     *
     * @ResponseSchema(name="V1_Identity_User_VerifyPrimaryEmail")
     */
    #[Route('/identity/user/verify-primary-email', name: 'V1_Identity_User_VerifyPrimaryEmail', methods: ['POST'])]
    public function verifyPrimaryEmail(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Identity_User_VerifyPrimaryEmail.json',
            'Response/V1_Identity_User_VerifyPrimaryEmail.json',
            VerifyPrimaryEmail::class,
            $this->verifyPrimaryEmailHandler,
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @RequestSchema(name="V1_Identity_User_RequestPrimaryEmailChange")
     *
     * @ResponseSchema(name="V1_Identity_User_RequestPrimaryEmailChange")
     */
    #[Route('/identity/user/request-primary-email-change', name: 'V1_Identity_User_RequestPrimaryEmailChange', methods: ['POST'])]
    public function requestPrimaryEmailChange(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Identity_User_RequestPrimaryEmailChange.json',
            'Response/V1_Identity_User_RequestPrimaryEmailChange.json',
            RequestPrimaryEmailChange::class,
            $this->requestPrimaryEmailChangeHandler,
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @RequestSchema(name="V1_Identity_User_ListSentVerificationEmail")
     *
     * @ResponseSchema(name="V1_Identity_User_ListSentVerificationEmail")
     */
    #[Route('/identity/user/list-sent-verification-emails', name: 'V1_Identity_User_ListSentVerificationEmails', methods: ['POST'])]
    public function listSentVerificationEmails(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Identity_User_ListSentVerificationEmail.json',
            'Response/V1_Identity_User_ListSentVerificationEmail.json',
            ListSentVerificationEmailQuery::class,
            $this->listSentVerificationEmailQueryHandler,
            null,
            Response::HTTP_OK
        );
    }
}
