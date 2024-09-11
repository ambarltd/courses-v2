<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\EndToEnd\BoundedContext\Identity;

use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\PrimaryEmailVerificationCode;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\EndToEnd\BaseHttpContext;
use Tests\Galeas\Api\EndToEnd\GaleasErrorResponse;
use Tests\Galeas\Api\EndToEnd\JsonResponse;

class IdentityContext extends BaseHttpContext
{
    /**
     * @var string
     */
    protected $verificationCode;

    /**
     * @Given that I want to sign up as a new user
     */
    public function thatIWantToSignUpAsANewUser(): void
    {
    }

    // User_SignUp

    /**
     * @When I provide sign up data :username, :password, :email
     */
    public function iProvideSignUpData(
        string $username,
        string $password,
        string $email
    ): void {
        $this->setNextInput(
            [
                'payload' => [
                    'username' => $username,
                    'password' => $password,
                    'primaryEmail' => $email,
                ],
            ]
        );
    }

    /**
     * @When I :accept the terms and conditions
     *
     * @throws \Exception
     */
    public function i_TheTermsAndConditions(string $accept): void
    {
        $nextInput = $this->getNextInput();

        switch ($accept) {
            case 'accept':
                $nextInput['payload']['termsOfUseAccepted'] = true;
                break;
            case 'do not accept':
                $nextInput['payload']['termsOfUseAccepted'] = false;
                break;
            default:
                throw new \Exception('Expecting "accept" or "do not accept"');
        }

        $this->setNextInput($nextInput);
    }

    /**
     * @When I request to sign up
     *
     * @throws \Exception
     */
    public function iRequestToSignUp(): void
    {
        $nextInput = $this->getNextInput();

        $nextInput['response'] = $this->makeJsonPostRequestAndGetResponse(
          'api/v1/identity/user/sign-up',
          $nextInput['payload']
        );

        $this->setNextInput($nextInput);
    }

    /**
     * @Then I should be able to sign up successfully
     *
     * @throws \Exception
     */
    public function iShouldBeAbleToSignUpSuccessfully(): void
    {
        $nextInput = $this->getNextInput();

        $response = $nextInput['response'];
        if (!($response instanceof JsonResponse)) {
            throw new \Exception('Could not find response');
        }

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput($nextInput);
    }

    /**
     * @Then I should receive a confirmation email at :email with a link containing a newly issued and random verification code
     *
     * @throws \Exception
     */
    public function iShouldReceiveAConfirmationEmailAt_WithALinkContainingANewlyIssuedAndRandomVerificationCode(string $email): void
    {
        // todo, check new event for email being sent

        $nextInput = $this->getNextInput();

        $response = $nextInput['response'];
        if (!($response instanceof JsonResponse)) {
            throw new \Exception('Could not find response');
        }

        $primaryEmailVerificationCode = self::getProjectionDocumentManager()
            ->getRepository(PrimaryEmailVerificationCode::class)
            ->findOneBy([
                'id' => $response->getDecodedJsonFromSuccessfulGaleasResponse()->userId,
            ]);

        if (!($primaryEmailVerificationCode instanceof PrimaryEmailVerificationCode)) {
            throw new \Exception('Could not find code');
        }
    }

    /**
     * @Then I should not be able to sign up successfully for the following reason :reason
     *
     * @throws \Exception
     */
    public function iShouldNotBeAbleToSignUpSuccessfullyForTheFollowingReason_(string $reason): void
    {
        $nextInput = $this->getNextInput();

        $response = $nextInput['response'];
        if (!($response instanceof JsonResponse)) {
            throw new \Exception('Could not find response');
        }

        switch ($reason) {
            case 'Username must be at least 3 characters long':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [
                            '[username] Must be at least 3 characters long',
                        ],
                        'json_schema_validation_error',
                        'Json Schema Validation Error'
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'Username must be at most 32 characters long':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [
                            '[username] Must be at most 32 characters long',
                        ],
                        'json_schema_validation_error',
                        'Json Schema Validation Error'
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'Username cannot have a special character':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Identity_User_SignUp_InvalidUsername',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'Password must be at least 10 characters long':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [
                            '[password] Must be at least 10 characters long',
                        ],
                        'json_schema_validation_error',
                        'Json Schema Validation Error'
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'Password must have at least one lowercase letter':
            case 'Password must have at least one uppercase letter':
            case 'Password must have at least one special character':
            case 'Password must have at least one number':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Identity_User_SignUp_InvalidPassword',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'Invalid email length':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [
                            '[primaryEmail] Must be at least 3 characters long',
                        ],
                        'json_schema_validation_error',
                        'Json Schema Validation Error'
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'Invalid email':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Identity_User_SignUp_InvalidEmail',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'You must agree with terms and conditions':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Identity_User_SignUp_TermsAreNotAgreedTo',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'The username is already in use':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Identity_User_SignUp_UsernameIsTaken',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'The email is already in use':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Identity_User_SignUp_EmailIsTaken',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            default:
                throw new \Exception('No error message found for '.$reason);
        }

        $this->setNextInput($nextInput);
    }

    /**
     * @Given that a registered user exists with :anotherUsername, :anotherPassword, :anotherEmail
     *
     * @throws \Exception
     */
    public function thatARegisteredUserExistsWith(
        string $anotherUsername,
        string $anotherPassword,
        string $anotherEmail
    ): void {
        $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/sign-up',
            [
                'username' => $anotherUsername,
                'password' => $anotherPassword,
                'primaryEmail' => $anotherEmail,
                'termsOfUseAccepted' => true,
            ]
        );
    }

    // User_PrimaryEmailChange

    /**
     * @Given that I signed up with username :username, password :password, and email :email
     *
     * @throws \Exception
     */
    public function thatISignedUpWithUsername_Password_AndEmail_(
        string $username,
        string $password,
        string $email
    ): void {
        $userId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/sign-up',
            [
                'username' => $username,
                'password' => $password,
                'primaryEmail' => $email,
                'termsOfUseAccepted' => true,
            ]
        )->getDecodedJsonFromSuccessfulGaleasResponse()->userId;

        $this->setNextInput([
            'userId' => $userId,
        ]);
    }

    /**
     * @Given I am signed in, with an unverified email, using email :email and password :password
     *
     * @throws \Exception
     */
    public function iAmSignedInWithAnUnverifiedEmailUsingEmail_AndPassword_(
        string $email,
        string $password
    ): void {
        $nextInput = $this->getNextInput();

        $nextInput['sessionToken'] = $this->signInAndObtainSessionToken($email, $password);

        $this->setNextInput($nextInput);
    }

    /**
     * @When I request to change my primary email to :email using password :password
     *
     * @throws \Exception
     */
    public function iRequestToChangeMyPrimaryEmail(string $email, string $password): void
    {
        $nextInput = $this->getNextInput();

        $nextInput['response'] = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/request-primary-email-change',
            [
                'newEmailRequested' => $email,
                'password' => $password,
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput($nextInput);
    }

    /**
     * @Then my request should be successful
     *
     * @throws \Exception
     */
    public function myRequestShouldBeSuccessful(): void
    {
        $nextInput = $this->getNextInput();

        $response = $nextInput['response'];

        if (!($response instanceof JsonResponse)) {
            throw new \Exception('Could not get response');
        }

        Assert::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @Then my request should fail because :reason
     *
     * @throws \Exception
     */
    public function myRequestShouldFailBecause_(string $reason): void
    {
        $nextInput = $this->getNextInput();

        $response = $nextInput['response'];
        if (!($response instanceof JsonResponse)) {
            throw new \Exception('Could not find response');
        }

        switch ($reason) {
            case 'the email is not changing':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Identity_User_RequestPrimaryEmailChange_EmailIsNotChanging',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'the password does not match':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Identity_User_RequestPrimaryEmailChange_PasswordDoesNotMatch',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'the email is taken':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Identity_User_RequestPrimaryEmailChange_EmailIsTaken',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'the email is invalid':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Identity_User_RequestPrimaryEmailChange_InvalidEmail',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'the verification code is incorrect':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Identity_User_VerifyPrimaryEmail_NoUserFoundForCode',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    404,
                    $response->getStatusCode()
                );
                break;
            default:
                throw new \Exception('Reason not found: '.$reason);
        }
    }

    /**
     * @Then my first request should be successful
     *
     * @throws \Exception
     */
    public function myFirstRequestShouldBeSuccessful(): void
    {
        $nextInput = $this->getNextInput();

        $response = $nextInput['firstResponse'];

        if (!($response instanceof JsonResponse)) {
            throw new \Exception('Could not get response');
        }

        Assert::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @Then my second request should fail because :reason
     *
     * @throws \Exception
     */
    public function mySecondRequestShouldFailBecause_(string $reason): void
    {
        $nextInput = $this->getNextInput();

        $response = $nextInput['secondResponse'];
        if (!($response instanceof JsonResponse)) {
            throw new \Exception('Could not find response');
        }

        switch ($reason) {
            case 'the code has been used':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Identity_User_VerifyPrimaryEmail_EmailIsAlreadyVerified',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            default:
                throw new \Exception('Reason not found: '.$reason);
        }
    }

    /**
     * @Given another user exists with username :username, password :password, and email :email
     *
     * @throws \Exception
     */
    public function anotherUnverifiedUserExistsWithUsername_Password_AndEmail_(
        string $username,
        string $password,
        string $email
    ): void {
        $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/sign-up',
            [
                'username' => $username,
                'password' => $password,
                'primaryEmail' => $email,
                'termsOfUseAccepted' => true,
            ]
        );
    }

    /**
     * @Given another user exists with username :username, password :password, and verified email :email
     *
     * @throws \Exception
     */
    public function anotherVerifiedUserExistsWithUsername_Password_AndEmail_(
        string $username,
        string $password,
        string $email
    ): void {
        $userId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/sign-up',
            [
                'username' => $username,
                'password' => $password,
                'primaryEmail' => $email,
                'termsOfUseAccepted' => true,
            ]
        )->getDecodedJsonFromSuccessfulGaleasResponse()->userId;

        $primaryEmailVerificationCode = $this->getPrimaryEmailVerificationCodeForUserId($userId);

        $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/verify-primary-email',
            [
                'verificationCode' => $primaryEmailVerificationCode,
            ]
        );
    }

    // VerifyPrimaryEmail

    /**
     * @Given that I have signed up with username :username, password :password, and email :email
     *
     * @throws \Exception
     */
    public function thatIHaveSignedUp(
        string $username,
        string $password,
        string $email
    ): void {
        $userId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/sign-up',
            [
                'username' => $username,
                'password' => $password,
                'primaryEmail' => $email,
                'termsOfUseAccepted' => true,
            ]
        )->getDecodedJsonFromSuccessfulGaleasResponse()->userId;

        $this->setNextInput([
            'userId' => $userId,
        ]);
    }

    /**
     * @Given I have successfully requested to change my primary email to :newEmailRequested with password :password
     *
     * @throws \Exception
     */
    public function iHaveSuccessfullyRequestedToChangeMyPrimaryEmailTo_WithPassword_(
        string $newEmailRequested,
        string $password
    ): void {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/request-primary-email-change',
            [
                'newEmailRequested' => $newEmailRequested,
                'password' => $password,
            ],
            $nextInput['sessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput($nextInput);
    }

    /**
     * @Given I request to verify that primary email change with the correct verification code
     *
     * @throws \Exception
     */
    public function iRequestToVerifyThatPrimaryEmailChangeWithTheCorrectVerificationCode(): void
    {
        $nextInput = $this->getNextInput();

        $nextInput['response'] = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/verify-primary-email',
            [
                'verificationCode' => $this->getPrimaryEmailVerificationCodeForUserId($nextInput['userId']),
            ]
        );

        $this->setNextInput($nextInput);
    }

    /**
     * @Given I request to verify that primary email change with verification code :verificationCode
     *
     * @throws \Exception
     */
    public function iRequestToVerifyThatPrimaryEmailChangeWithVerificationCode_(string $verificationCode): void
    {
        $nextInput = $this->getNextInput();

        $nextInput['response'] = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/verify-primary-email',
            [
                'verificationCode' => $verificationCode,
            ]
        );

        $this->setNextInput($nextInput);
    }

    /**
     * @Given I request to verify that primary email with the correct verification code
     *
     * @throws \Exception
     */
    public function iRequestToVerifyThatPrimaryEmailWithTheCorrectVerificationCode(): void
    {
        $nextInput = $this->getNextInput();

        $nextInput['response'] = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/verify-primary-email',
            [
                'verificationCode' => $this->getPrimaryEmailVerificationCodeForUserId($nextInput['userId']),
            ]
        );

        $this->setNextInput($nextInput);
    }

    /**
     * @Given I request to verify that primary email change with the correct verification code twice
     *
     * @throws \Exception
     */
    public function iRequestToVerifyThatPrimaryEmailChangeWithTheCorrectVerificationCodeTwice(): void
    {
        $nextInput = $this->getNextInput();

        $verificationCode = $this->getPrimaryEmailVerificationCodeForUserId($nextInput['userId']);

        $nextInput['firstResponse'] = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/verify-primary-email',
            [
                'verificationCode' => $verificationCode,
            ]
        );
        $nextInput['secondResponse'] = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/verify-primary-email',
            [
                'verificationCode' => $verificationCode,
            ]
        );

        $this->setNextInput($nextInput);
    }

    /**
     * @Given I request to verify that primary email with the correct verification code twice
     *
     * @throws \Exception
     */
    public function iRequestToVerifyThatPrimaryEmailWithTheCorrectVerificationCodeTwice(): void
    {
        $nextInput = $this->getNextInput();

        $verificationCode = $this->getPrimaryEmailVerificationCodeForUserId($nextInput['userId']);

        $nextInput['firstResponse'] = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/verify-primary-email',
            [
                'verificationCode' => $verificationCode,
            ]
        );
        $nextInput['secondResponse'] = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/verify-primary-email',
            [
                'verificationCode' => $verificationCode,
            ]
        );

        $this->setNextInput($nextInput);
    }

    /**
     * @Then I should be able to sign in with email :email and password :password
     *
     * @throws \Exception
     */
    public function iShouldBeAbleToSignInWith_And(
        string $email,
        string $password
    ): void {
        $this->signInAndObtainSessionToken($email, $password);
    }

    /**
     * @Then I should not be able to sign in with email :email and password :password
     *
     * @throws \Exception
     */
    public function iShouldNotBeAbleToSignInWith_And(
        string $email,
        string $password
    ): void {
        try {
            $this->signInAndObtainSessionToken($email, $password);
        } catch (\Exception $exception) {
            if ('Cannot obtain session token' == $exception->getMessage()) {
                return;
            }
        }

        throw new \Exception('Expected not being able to login');
    }

    /**
     * @param string $usernameOrEmail
     * @param string $password
     *
     * @throws \Exception
     */
    protected function signInAndObtainSessionToken($usernameOrEmail, $password): string
    {
        $payload = [
            'withUsernameOrEmail' => $usernameOrEmail,
            'withPassword' => $password,
            'byDeviceLabel' => 'whatDeviceLabel',
        ];

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/security/session/sign-in',
            $payload
        );

        try {
            $decodedResponse = $response->getDecodedJsonFromSuccessfulGaleasResponse();
            if (
                property_exists($decodedResponse, 'sessionTokenCreated') &&
                is_string($decodedResponse->sessionTokenCreated)
            ) {
                return $decodedResponse->sessionTokenCreated;
            }
        } catch (\Throwable $exception) {
        }

        throw new \Exception('Cannot obtain session token');
    }

    /**
     * @throws \Exception
     */
    protected function getPrimaryEmailVerificationCodeForUserId(string $userId): string
    {
        $primaryEmailVerificationCode = self::getProjectionDocumentManager()
            ->getRepository(PrimaryEmailVerificationCode::class)
            ->findOneBy([
                'id' => $userId,
            ]);

        if (is_object($primaryEmailVerificationCode)) {
            // the verification code might have changed, query the database every time to get the latest code.
            self::getProjectionDocumentManager()->refresh($primaryEmailVerificationCode);
        }

        if (
            $primaryEmailVerificationCode instanceof PrimaryEmailVerificationCode &&
            null !== $primaryEmailVerificationCode->getPrimaryEmailVerificationCode()
        ) {
            return $primaryEmailVerificationCode->getPrimaryEmailVerificationCode();
        }

        throw new \Exception('Could not find PrimaryEmailVerificationCode');
    }

    /**
     * @Given I am signed in, with a verified email, using email :email and password :password
     *
     * @throws \Exception
     */
    public function iAmSignedInWithAVerifiedEmailUsingEmail_AndPassword_(
        string $email,
        string $password
    ): void {
        $nextInput = $this->getNextInput();

        $nextInput['sessionToken'] = $this->signInAndObtainSessionToken($email, $password);

        $primaryEmailVerificationCode = $this->getPrimaryEmailVerificationCodeForUserId($nextInput['userId']);

        $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/verify-primary-email',
            [
                'verificationCode' => $primaryEmailVerificationCode,
            ]
        );

        $this->setNextInput($nextInput);
    }
}
