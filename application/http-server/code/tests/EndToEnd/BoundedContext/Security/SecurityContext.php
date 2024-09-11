<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\EndToEnd\BoundedContext\Security;

use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\PrimaryEmailVerificationCode;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\EndToEnd\BaseHttpContext;
use Tests\Galeas\Api\EndToEnd\GaleasErrorResponse;
use Tests\Galeas\Api\EndToEnd\JsonResponse;

class SecurityContext extends BaseHttpContext
{
    /**
     * ******************************************************************
     * Signing up
     * ******************************************************************.
     */

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
            'username' => $username,
            'userId' => $userId,
            'password' => $password,
            'email' => $email,
        ]);
    }

    /**
     * ******************************************************************
     * Signing in
     * ******************************************************************.
     */

    /**
     * @Given I am signed in, with a verified email, using email :email and password :password
     * @Given I am signed in using email :email and password :password
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

    /**
     * @When I request to sign in with username :username and password :password
     */
    public function iRequestToSignInWithUsernameAndPassword($username, $password): void
    {
        $nextInput = $this->getNextInput();

        try {
            $this->signInAndObtainSessionToken($username, $password);

            //Need to re-read nextInput, since signInAndObtainSessionToken may have modified it.
            $nextInput = $this->getNextInput();
            $nextInput['signInSuccessful'] = true;
        } catch (\Exception $e) {
            $nextInput = $this->getNextInput();
            $nextInput['signInSuccessful'] = false;
        }

        $this->setNextInput($nextInput);
    }

    /**
     * @When I request to sign in with email :email and password :password
     */
    public function iRequestToSignInWithEmailAndPassword($email, $password): void
    {
        $nextInput = $this->getNextInput();

        try {
            $this->signInAndObtainSessionToken($email, $password);

            //Need to re-read nextInput, since signInAndObtainSessionToken may have modified it.
            $nextInput = $this->getNextInput();
            $nextInput['signInSuccessful'] = true;
        } catch (\Exception $e) {
            $nextInput = $this->getNextInput();
            $nextInput['signInSuccessful'] = false;
        }

        $this->setNextInput($nextInput);
    }

    /**
     * @When I request to sign in with email :email and no password
     */
    public function iRequestToSignInWithEmailAndNoPassword($email): void
    {
        $nextInput = $this->getNextInput();

        try {
            $this->signInAndObtainSessionToken($email, null);

            //Need to re-read nextInput, since signInAndObtainSessionToken may have modified it.
            $nextInput = $this->getNextInput();
            $nextInput['signInSuccessful'] = true;
        } catch (\Exception $e) {
            $nextInput = $this->getNextInput();
            $nextInput['signInSuccessful'] = false;
        }

        $this->setNextInput($nextInput);
    }

    /**
     * @When I request to sign in without either an email or username
     */
    public function iRequestToSignInWithoutEitherAnEmailOrUsername(): void
    {
        $nextInput = $this->getNextInput();

        try {
            $this->signInAndObtainSessionToken(null, $nextInput['password']);

            //Need to re-read nextInput, since signInAndObtainSessionToken may have modified it.
            $nextInput = $this->getNextInput();
            $nextInput['signInSuccessful'] = true;
        } catch (\Exception $e) {
            $nextInput = $this->getNextInput();
            $nextInput['signInSuccessful'] = false;
        }

        $this->setNextInput($nextInput);
    }

    /**
     * ******************************************************************
     * Refreshing session token
     * ******************************************************************.
     */

    /**
     * @Given I have refreshed my session token
     */
    public function iHaveRefreshedMySessionToken(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/security/session/refresh-token',
            [],
            $nextInput['sessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $nextInput['response'] = $response;
        $nextInput['sessionToken'] = $response->getDecodedJsonFromSuccessfulGaleasResponse()->refreshedSessionToken;

        $this->setNextInput($nextInput);
    }

    /**
     * @When I request to refresh my session token (again)
     */
    public function iRequestToRefreshMySessionToken(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/security/session/refresh-token',
            [],
            $nextInput['sessionToken']
        );

        $nextInput['response'] = $response;

        $this->setNextInput($nextInput);
    }

    /**
     * ******************************************************************
     * Signing out
     * ******************************************************************.
     */

    /**
     * @Given I have signed out
     * @Given I am signed out
     * @When I sign out
     */
    public function iHaveSignedOut(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/security/session/sign-out',
            [],
            $nextInput['sessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @When I request to sign out (again)
     */
    public function iRequestToSignOut(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/security/session/sign-out',
            [],
            $nextInput['sessionToken']
        );

        $nextInput['response'] = $response;

        $this->setNextInput($nextInput);
    }

    /**
     * ******************************************************************
     * Checking responses
     * ******************************************************************.
     */

    /**
     * @Then my request should succeed
     * @Then my request should be successful
     */
    public function myRequestShouldSucceed(): void
    {
        $nextInput = $this->getNextInput();

        $response = $nextInput['response'];

        Assert::assertEquals(200, $response->getStatusCode()
        );
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
            case 'I am not signed in':
                Assert::assertEquals(
                    'Service_RequestMapper_MissingExpectedSessionToken',
                    $response->getDecodedJsonAsGaleasErrorResponse()->getErrorIdentifier()
                );

                Assert::assertEquals(
                    401,
                    $response->getStatusCode()
                );
                break;
            case 'the username is incorrect':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Security_Session_SignIn_UserNotFound',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'the password is incorrect':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Security_Session_SignIn_InvalidPassword',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'the email is incorrect':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Security_Session_SignIn_UserNotFound',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'the password is missing':
                Assert::assertEquals('json_schema_validation_error', $response->getDecodedJsonAsGaleasErrorResponse()->getErrorIdentifier());
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'the username or email is missing':
                Assert::assertEquals('json_schema_validation_error',
                    $response->getDecodedJsonAsGaleasErrorResponse()->getErrorIdentifier()
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
     * ******************************************************************
     * Helper functions
     * ******************************************************************.
     */

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
     * @param string|null $usernameOrEmail
     * @param string|null $password
     *
     * @throws \Exception
     */
    protected function signInAndObtainSessionToken($usernameOrEmail, $password): string
    {
        $nextInput = $this->getNextInput();

        $payload = [
            'withUsernameOrEmail' => $usernameOrEmail,
            'withPassword' => $password,
            'byDeviceLabel' => 'whatDeviceLabel',
        ];

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/security/session/sign-in',
            $payload
        );

        $nextInput['response'] = $response;
        $this->setNextInput($nextInput);

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
}
