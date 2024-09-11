<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\EndToEnd\BoundedContext\Messaging;

use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\PrimaryEmailVerificationCode;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\EndToEnd\BaseHttpContext;
use Tests\Galeas\Api\EndToEnd\GaleasErrorResponse;
use Tests\Galeas\Api\EndToEnd\JsonResponse;

class MessagingContext extends BaseHttpContext
{
    /**
     * @Given I am signed in with a verified email
     *
     * @throws \Exception
     */
    public function thatIAmSignedInWithAVerifiedEmail(): void
    {
        $username = 'MyUsername';
        $password = 'Test12345#';
        $email = 'my_email@galeas.com';

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

        $this->setNextInput([
            'mySessionToken' => $this->signInAndObtainSessionToken($email, $password),
            'myUserId' => $userId,
        ]);
    }

    /**
     * @Given JohnDoe is also an existing user
     *
     * @throws \Exception
     */
    public function johnDoeIsAlsoAnExistingUser(): void
    {
        $nextInput = $this->getNextInput();

        $username = 'JohnDoe';
        $password = 'Test12345#';
        $email = 'john_doe@galeas.com';

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

        $this->setNextInput([
            'mySessionToken' => $nextInput['mySessionToken'],
            'myUserId' => $nextInput['myUserId'],
            'johnDoeSessionToken' => $this->signInAndObtainSessionToken($email, $password),
            'johnDoeUserId' => $userId,
        ]);
    }

    /**
     * @When JohnDoe requests to accept my contact request
     */
    public function johnDoeRequestsToAcceptMyContactRequest(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/accept-contact-request',
            [
                'acceptedContact' => $nextInput['myUserId'],
            ],
            $nextInput['johnDoeSessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @Given JohnDoe has requested to be my contact
     */
    public function johnDoeHasRequestedToBeMyContact(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/request-contact',
            [
                'requestedContact' => $nextInput['myUserId'],
            ],
            $nextInput['johnDoeSessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'mySessionToken' => $nextInput['mySessionToken'],
            'myUserId' => $nextInput['myUserId'],
            'johnDoeSessionToken' => $nextInput['johnDoeSessionToken'],
            'johnDoeUserId' => $nextInput['johnDoeUserId'],
        ]);
    }

    /**
     * @Given I have accepted JohnDoe's contact request
     */
    public function iHaveAcceptedJohnDoesContactRequest(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/accept-contact-request',
            [
                'acceptedContact' => $nextInput['johnDoeUserId'],
            ],
            $nextInput['mySessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'mySessionToken' => $nextInput['mySessionToken'],
            'myUserId' => $nextInput['myUserId'],
            'johnDoeSessionToken' => $nextInput['johnDoeSessionToken'],
            'johnDoeUserId' => $nextInput['johnDoeUserId'],
        ]);
    }

    /**
     * @When I request to accept JohnDoe's contact request
     */
    public function iRequestToAcceptJohnDoesContactRequest(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/accept-contact-request',
            [
                'acceptedContact' => $nextInput['johnDoeUserId'],
            ],
            $nextInput['mySessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @Given JohnDoe has cancelled his contact request
     */
    public function johnDoeHasCancelledHisContactRequest(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/cancel-contact-request',
            [
                'cancelledContact' => $nextInput['myUserId'],
            ],
            $nextInput['johnDoeSessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'mySessionToken' => $nextInput['mySessionToken'],
            'myUserId' => $nextInput['myUserId'],
            'johnDoeSessionToken' => $nextInput['johnDoeSessionToken'],
            'johnDoeUserId' => $nextInput['johnDoeUserId'],
        ]);
    }

    /**
     * @Given I deleted JohnDoe as a contact
     */
    public function iDeletedJohnDoeAsAContact(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/delete-contact',
            [
                'deletedContact' => $nextInput['johnDoeUserId'],
            ],
            $nextInput['mySessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'mySessionToken' => $nextInput['mySessionToken'],
            'myUserId' => $nextInput['myUserId'],
            'johnDoeSessionToken' => $nextInput['johnDoeSessionToken'],
            'johnDoeUserId' => $nextInput['johnDoeUserId'],
        ]);
    }

    /**
     * @Given JohnDoe deleted me as a contact
     */
    public function johnDoeDeletedMeAsAContact(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/delete-contact',
            [
                'deletedContact' => $nextInput['myUserId'],
            ],
            $nextInput['johnDoeSessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'mySessionToken' => $nextInput['mySessionToken'],
            'myUserId' => $nextInput['myUserId'],
            'johnDoeSessionToken' => $nextInput['johnDoeSessionToken'],
            'johnDoeUserId' => $nextInput['johnDoeUserId'],
        ]);
    }

    /**
     * @Given I rejected JohnDoe's contact request
     */
    public function iRejectedJohnDoesContactRequest(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/reject-contact-request',
            [
                'rejectedContact' => $nextInput['johnDoeUserId'],
            ],
            $nextInput['mySessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'mySessionToken' => $nextInput['mySessionToken'],
            'myUserId' => $nextInput['myUserId'],
            'johnDoeSessionToken' => $nextInput['johnDoeSessionToken'],
            'johnDoeUserId' => $nextInput['johnDoeUserId'],
        ]);
    }

    /**
     * @Given JaneDoe is also an existing user
     *
     * @throws \Exception
     */
    public function janeDoeIsAlsoAnExistingUser(): void
    {
        $nextInput = $this->getNextInput();

        $username = 'JaneDoe';
        $password = 'Test12345#';
        $email = 'jane_doe@galeas.com';

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

        $this->setNextInput([
            'mySessionToken' => $nextInput['mySessionToken'],
            'myUserId' => $nextInput['myUserId'],
            'janeDoeSessionToken' => $this->signInAndObtainSessionToken($email, $password),
            'janeDoeUserId' => $userId,
        ]);
    }

    /**
     * @When JaneDoe requests to cancel my contact request
     */
    public function janeDoeRequestsToCancelMyContactRequest(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/cancel-contact-request',
            [
                'cancelledContact' => $nextInput['myUserId'],
            ],
            $nextInput['janeDoeSessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @Given I have requested to be a contact of JaneDoe
     */
    public function iHaveRequestedToBeAContactOfJaneDoe(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/request-contact',
            [
                'requestedContact' => $nextInput['janeDoeUserId'],
            ],
            $nextInput['mySessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'mySessionToken' => $nextInput['mySessionToken'],
            'myUserId' => $nextInput['myUserId'],
            'janeDoeSessionToken' => $nextInput['janeDoeSessionToken'],
            'janeDoeUserId' => $nextInput['janeDoeUserId'],
        ]);
    }

    /**
     * @Given JaneDoe has accepted my contact request
     */
    public function janeDoeHasAcceptedMyContactRequest(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/accept-contact-request',
            [
                'acceptedContact' => $nextInput['myUserId'],
            ],
            $nextInput['janeDoeSessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'mySessionToken' => $nextInput['mySessionToken'],
            'myUserId' => $nextInput['myUserId'],
            'janeDoeSessionToken' => $nextInput['janeDoeSessionToken'],
            'janeDoeUserId' => $nextInput['janeDoeUserId'],
        ]);
    }

    /**
     * @When I request to cancel my contact request to JaneDoe
     */
    public function iRequestToCancelMyContactRequestToJaneDoe(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/cancel-contact-request',
            [
                'cancelledContact' => $nextInput['janeDoeUserId'],
            ],
            $nextInput['mySessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @Given I have cancelled my contact request to JaneDoe
     */
    public function iHaveCancelledMyContactRequestToJaneDoe(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/cancel-contact-request',
            [
                'cancelledContact' => $nextInput['janeDoeUserId'],
            ],
            $nextInput['mySessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'mySessionToken' => $nextInput['mySessionToken'],
            'myUserId' => $nextInput['myUserId'],
            'janeDoeSessionToken' => $nextInput['janeDoeSessionToken'],
            'janeDoeUserId' => $nextInput['janeDoeUserId'],
        ]);
    }

    /**
     * @Given JaneDoe has deleted me as a contact
     */
    public function janeDoeHasDeletedMeAsAContact(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/delete-contact',
            [
                'deletedContact' => $nextInput['myUserId'],
            ],
            $nextInput['janeDoeSessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'mySessionToken' => $nextInput['mySessionToken'],
            'myUserId' => $nextInput['myUserId'],
            'janeDoeSessionToken' => $nextInput['janeDoeSessionToken'],
            'janeDoeUserId' => $nextInput['janeDoeUserId'],
        ]);
    }

    /**
     * @Given I have deleted JaneDoe as a contact
     */
    public function iHaveDeletedJaneDoeAsAContact(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/delete-contact',
            [
                'deletedContact' => $nextInput['janeDoeUserId'],
            ],
            $nextInput['mySessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'mySessionToken' => $nextInput['mySessionToken'],
            'myUserId' => $nextInput['myUserId'],
            'janeDoeSessionToken' => $nextInput['janeDoeSessionToken'],
            'janeDoeUserId' => $nextInput['janeDoeUserId'],
        ]);
    }

    /**
     * @Given JaneDoe has rejected my contact request
     */
    public function janeDoeHasRejectedMyContactRequest(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/reject-contact-request',
            [
                'rejectedContact' => $nextInput['myUserId'],
            ],
            $nextInput['janeDoeSessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'mySessionToken' => $nextInput['mySessionToken'],
            'myUserId' => $nextInput['myUserId'],
            'janeDoeSessionToken' => $nextInput['janeDoeSessionToken'],
            'janeDoeUserId' => $nextInput['janeDoeUserId'],
        ]);
    }

    /**
     * @When JohnDoe requests to delete me as a contact
     */
    public function johnDoeRequestsToDeleteMeAsAContact(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/delete-contact',
            [
                'deletedContact' => $nextInput['myUserId'],
            ],
            $nextInput['johnDoeSessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to delete JohnDoe as a contact
     */
    public function iRequestToDeleteJohnDoeAsAContact(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/delete-contact',
            [
                'deletedContact' => $nextInput['johnDoeUserId'],
            ],
            $nextInput['mySessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When JohnDoe requests to reject my contact request
     */
    public function johnDoeRequestsToRejectMyContactRequest(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/reject-contact-request',
            [
                'rejectedContact' => $nextInput['myUserId'],
            ],
            $nextInput['johnDoeSessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to reject JohnDoe's contact request
     */
    public function iRequestToRejectJohnDoesContactRequest(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/reject-contact-request',
            [
                'rejectedContact' => $nextInput['johnDoeUserId'],
            ],
            $nextInput['mySessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to be my own contact
     */
    public function iRequestToBeMyOwnContact(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/request-contact',
            [
                'requestedContact' => $nextInput['myUserId'],
            ],
            $nextInput['mySessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to be a contact of a non-existing user
     */
    public function iRequestToBeAContactOfANonExistingUser(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/request-contact',
            [
                'requestedContact' => 'TsPJVd4ohuC0iz-q75rLjm5u8gYoJ0FYi9y3uOrHNJzT6WJAYdlh82ES',
            ],
            $nextInput['mySessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to be a contact of JaneDoe
     */
    public function iRequestToBeAContactOfJaneDoe(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/request-contact',
            [
                'requestedContact' => $nextInput['janeDoeUserId'],
            ],
            $nextInput['mySessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to see a list of my contacts
     */
    public function iRequestToSeeAListOfMyContacts(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/list-contacts',
            [
            ],
            $nextInput['mySessionToken']
        );

        $this->setNextInput([
            'response' => $response,
            'myUserId' => $nextInput['myUserId'],
            'johnDoeUserId' => $nextInput['johnDoeUserId'],
        ]);
    }

    /**
     * @Then I should have no requested contacts
     * @Then JohnDoe should have no requested contacts
     *
     * @throws \Exception
     */
    public function iShouldHaveNoRequestedContacts(): void
    {
        $nextInput = $this->getNextInput();

        $responseArray = $this->obtainJsonResponseFromArray($nextInput)
            ->getDecodedJsonArrayFromSuccessfulGaleasResponse();

        Assert::assertEquals(
            [
                'requestedContacts',
                'requestingContacts',
                'activeContacts',
            ],
            array_keys($responseArray)
        );
        Assert::assertEquals(
            [],
            $responseArray['requestedContacts']
        );
    }

    /**
     * @Then I should have no requesting contacts
     * @Then JohnDoe should have no requesting contacts
     *
     * @throws \Exception
     */
    public function iShouldHaveNoRequestingContacts(): void
    {
        $nextInput = $this->getNextInput();

        $responseArray = $this->obtainJsonResponseFromArray($nextInput)
            ->getDecodedJsonArrayFromSuccessfulGaleasResponse();

        Assert::assertEquals(
            [
                'requestedContacts',
                'requestingContacts',
                'activeContacts',
            ],
            array_keys($responseArray)
        );
        Assert::assertEquals(
            [],
            $responseArray['requestingContacts']
        );
    }

    /**
     * @Then I should have no active contacts
     * @Then JohnDoe should have no active contacts
     *
     * @throws \Exception
     */
    public function iShouldHaveNoActiveContacts(): void
    {
        $nextInput = $this->getNextInput();

        $responseArray = $this->obtainJsonResponseFromArray($nextInput)
            ->getDecodedJsonArrayFromSuccessfulGaleasResponse();

        Assert::assertEquals(
            [
                'requestedContacts',
                'requestingContacts',
                'activeContacts',
            ],
            array_keys($responseArray)
        );
        Assert::assertEquals(
            [],
            $responseArray['activeContacts']
        );
    }

    /**
     * @When JohnDoe requests to see a list of his contacts
     */
    public function johnDoeRequestsToSeeAListOfHisContacts(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/messaging/contact/list-contacts',
            [
            ],
            $nextInput['johnDoeSessionToken']
        );

        $this->setNextInput([
            'response' => $response,
            'myUserId' => $nextInput['myUserId'],
            'johnDoeUserId' => $nextInput['johnDoeUserId'],
        ]);
    }

    /**
     * @Then JohnDoe should be my only active contact
     *
     * @throws \Exception
     */
    public function johnDoeShouldBeMyOnlyActiveContacts(): void
    {
        $nextInput = $this->getNextInput();

        $responseArray = $this->obtainJsonResponseFromArray($nextInput)
            ->getDecodedJsonArrayFromSuccessfulGaleasResponse();

        Assert::assertEquals(
            [
                'requestedContacts',
                'requestingContacts',
                'activeContacts',
            ],
            array_keys($responseArray)
        );
        Assert::assertEquals(
            [
                [
                    'id' => $nextInput['johnDoeUserId'],
                    'username' => 'JohnDoe',
                ],
            ],
            $responseArray['activeContacts']
        );
    }

    /**
     * @Then I should be JohnDoe's only active contact
     *
     * @throws \Exception
     */
    public function iShouldBeJohnDoesOnlyActiveContact(): void
    {
        $nextInput = $this->getNextInput();

        $responseArray = $this->obtainJsonResponseFromArray($nextInput)
            ->getDecodedJsonArrayFromSuccessfulGaleasResponse();

        Assert::assertEquals(
            [
                'requestedContacts',
                'requestingContacts',
                'activeContacts',
            ],
            array_keys($responseArray)
        );
        Assert::assertEquals(
            [
                [
                    'id' => $nextInput['myUserId'],
                    'username' => 'MyUsername',
                ],
            ],
            $responseArray['activeContacts']
        );
    }

    /**
     * @Then JohnDoe should be my only requesting contact
     *
     * @throws \Exception
     */
    public function johnDoeShouldBeMyOnlyRequestingContact(): void
    {
        $nextInput = $this->getNextInput();

        $responseArray = $this->obtainJsonResponseFromArray($nextInput)
            ->getDecodedJsonArrayFromSuccessfulGaleasResponse();

        Assert::assertEquals(
            [
                'requestedContacts',
                'requestingContacts',
                'activeContacts',
            ],
            array_keys($responseArray)
        );
        Assert::assertEquals(
            [
                [
                    'id' => $nextInput['johnDoeUserId'],
                    'username' => 'JohnDoe',
                ],
            ],
            $responseArray['requestingContacts']
        );
    }

    /**
     * @Then I should be JohnDoe's only requested contact
     *
     * @throws \Exception
     */
    public function iShouldBeJohnDoesOnlyRequestedContact(): void
    {
        $nextInput = $this->getNextInput();

        $responseArray = $this->obtainJsonResponseFromArray($nextInput)
            ->getDecodedJsonArrayFromSuccessfulGaleasResponse();

        Assert::assertEquals(
            [
                'requestedContacts',
                'requestingContacts',
                'activeContacts',
            ],
            array_keys($responseArray)
        );
        Assert::assertEquals(
            [
                [
                    'id' => $nextInput['myUserId'],
                    'username' => 'MyUsername',
                ],
            ],
            $responseArray['requestedContacts']
        );
    }

    /**
     * @Then my request should fail because :reason
     * @Then his request should fail because :reason
     * @Then her request should fail because :reason
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
            case 'he cannot accept a contact request that has not been made':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_AcceptContactRequest_ContactDoesNotExistYet',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'he cannot accept a contact request he requested':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_AcceptContactRequest_AccepterIsNotRequested',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'I cannot accept a contact request from an already active contact':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_AcceptContactRequest_ContactIsActive',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'I cannot accept a contact request after cancelling the contact request':
            case 'I cannot accept a contact request after having deleted the contact':
            case 'I cannot accept a contact request after being deleted by that contact':
            case 'I cannot accept a contact request after the request was rejected':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_AcceptContactRequest_ContactIsInactive',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'there is no contact request to be cancelled':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_CancelContactRequest_ContactDoesNotExistYet',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'only I can cancel the contact request':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_CancelContactRequest_CancellerIsNotRequester',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'I cannot cancel a contact request to an already active contact':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_CancelContactRequest_ContactIsActive',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'I cannot cancel a contact request that has already been cancelled':
            case 'I cannot cancel a contact request after having been deleted as a contact':
            case 'I cannot cancel a contact request after having deleted the contact':
            case 'I cannot cancel a contact request after having rejected it':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_CancelContactRequest_ContactIsInactive',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'he cannot delete as a contact, someone who has never been a contact':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_DeleteContact_ContactDoesNotExistYet',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'I cannot delete a contact after it cancelled its contact request':
            case 'I cannot delete a contact that has already been deleted':
            case 'I cannot delete a contact that has deleted me as a contact':
            case 'I cannot delete a contact after I rejected its contact request':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_DeleteContact_ContactIsInactive',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'I cannot delete a contact with a pending contact request':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_DeleteContact_ContactIsPending',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'there is no contact request to be rejected':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_RejectContactRequest_ContactDoesNotExistYet',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'only I can reject the contact request':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_RejectContactRequest_RejecterIsNotRequested',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'I cannot reject a contact request to an already active contact':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_RejectContactRequest_ContactIsActive',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'I cannot reject a contact request after the requester cancelled it':
            case 'I cannot reject a contact request after having deleted the contact':
            case 'I cannot reject a contact request after having been deleted as a contact':
            case 'I cannot reject a contact request that has already been rejected':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_RejectContactRequest_ContactIsInactive',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'I cannot request to be my own contact':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_RequestContact_CannotRequestSelf',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'a user that does not exist cannot be requested as a contact':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_RequestContact_RequestedContactDoesNotExist',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    404,
                    $response->getStatusCode()
                );
                break;
            case 'I cannot request to be a contact of an already active contact':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_RequestContact_ContactIsActive',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'I cannot request to be a contact of an already pending contact':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Messaging_Contact_RequestContact_ContactIsPending',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            default:
                throw new \Exception('Reason not found: '.$reason);
        }
    }

    /**
     * @Then my request should be successful
     *
     * @throws \Exception
     */
    public function myRequestShouldBeSuccessful(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->obtainJsonResponseFromArray($nextInput);

        Assert::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @throws \Exception
     */
    private function obtainJsonResponseFromArray(array $array): JsonResponse
    {
        $response = $array['response'];

        if (!($response instanceof JsonResponse)) {
            throw new \Exception('Could not get response');
        }

        return $response;
    }

    /**
     * @param string $usernameOrEmail
     * @param string $password
     *
     * @throws \Exception
     */
    private function signInAndObtainSessionToken($usernameOrEmail, $password): string
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
    private function getPrimaryEmailVerificationCodeForUserId(string $userId): string
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
}
