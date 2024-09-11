<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\EndToEnd\BoundedContext\Library;

use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\PrimaryEmailVerificationCode;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\EndToEnd\BaseHttpContext;
use Tests\Galeas\Api\EndToEnd\GaleasErrorResponse;
use Tests\Galeas\Api\EndToEnd\JsonResponse;

class LibraryContext extends BaseHttpContext
{
    /**
     * @Given I am signed in with a verified email
     *
     * @throws \Exception
     */
    public function thatIAmSignedInWithAVerifiedEmail(): void
    {
        $username = 'Test12345';
        $password = 'Test12345#';
        $email = 'test@galeas.com';

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
            'sessionToken' => $this->signInAndObtainSessionToken($email, $password),
        ]);
    }

    /**
     * @When I request to create a folder with name :name
     *
     * @throws \Exception
     */
    public function iRequestToCreateAFolderWithName_(string $name): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'parentId' => null,
                'name' => $name,
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to create a folder without a name
     *
     * @throws \Exception
     */
    public function iRequestToCreateAFolderWithoutAName(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'parentId' => null,
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @Given I have created a folder with name GreatGrandParent
     *
     * @throws \Exception
     */
    public function iHaveCreatedAFolderWithNameGreatGrandParent(): void
    {
        $nextInput = $this->getNextInput();

        $greatGrandParentFolderId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'GreatGrandParent',
                'parentId' => null,
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'greatGrandParentFolderId' => $greatGrandParentFolderId,
        ]);
    }

    /**
     * @Given I have created a folder with name GrandParent under folder GreatGrandParent
     *
     * @throws \Exception
     */
    public function iHaveCreatedAFolderWithNameGrandParentUnderFolderGreatGrandParent(): void
    {
        $nextInput = $this->getNextInput();

        $grandParentFolderId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'GrandParent',
                'parentId' => $nextInput['greatGrandParentFolderId'],
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'greatGrandParentFolderId' => $nextInput['greatGrandParentFolderId'],
            'grandParentFolderId' => $grandParentFolderId,
        ]);
    }

    /**
     * @Given I have created a folder with name Parent under folder GrandParent
     *
     * @throws \Exception
     */
    public function iHaveCreatedAFolderWithNameParentUnderFolderGrandParent(): void
    {
        $nextInput = $this->getNextInput();

        $parentFolderId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'Parent',
                'parentId' => $nextInput['grandParentFolderId'],
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'greatGrandParentFolderId' => $nextInput['greatGrandParentFolderId'],
            'grandParentFolderId' => $nextInput['grandParentFolderId'],
            'parentFolderId' => $parentFolderId,
        ]);
    }

    /**
     * @When I have created a folder with name Child under Parent
     *
     * @throws \Exception
     */
    public function iHaveCreatedAFolderWithNameChildUnderParent(): void
    {
        $nextInput = $this->getNextInput();

        $childFolderId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'Child',
                'parentId' => $nextInput['parentFolderId'],
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'childFolderId' => $childFolderId,
            'parentFolderId' => $nextInput['parentFolderId'],
            'grandParentFolderId' => $nextInput['grandParentFolderId'],
            'greatGrandParentFolderId' => $nextInput['greatGrandParentFolderId'],
        ]);
    }

    /**
     * @Given I have deleted folder GreatGrandParent
     *
     * @throws \Exception
     */
    public function iHaveDeletedFolderGreatGrandParent(): void
    {
        $nextInput = $this->getNextInput();

        $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['greatGrandParentFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'greatGrandParentFolderId' => $nextInput['greatGrandParentFolderId'],
            'grandParentFolderId' => $nextInput['grandParentFolderId'],
            'parentFolderId' => $nextInput['parentFolderId'],
            'childFolderId' => $nextInput['childFolderId'],
        ]);
    }

    /**
     * @Given I have deleted folder GrandParent
     *
     * @throws \Exception
     */
    public function iHaveDeletedFolderGrandParent(): void
    {
        $nextInput = $this->getNextInput();

        $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['grandParentFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'greatGrandParentFolderId' => $nextInput['greatGrandParentFolderId'],
            'grandParentFolderId' => $nextInput['grandParentFolderId'],
            'parentFolderId' => $nextInput['parentFolderId'],
            'childFolderId' => $nextInput['childFolderId'],
        ]);
    }

    /**
     * @Given I have deleted folder Parent
     *
     * @throws \Exception
     */
    public function iHaveDeletedFolderParent(): void
    {
        $nextInput = $this->getNextInput();

        $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['parentFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'greatGrandParentFolderId' => $nextInput['greatGrandParentFolderId'],
            'grandParentFolderId' => $nextInput['grandParentFolderId'],
            'parentFolderId' => $nextInput['parentFolderId'],
            'childFolderId' => $nextInput['childFolderId'],
        ]);
    }

    /**
     * @Given I have deleted folder Child
     *
     * @throws \Exception
     */
    public function iHaveDeletedFolderChild(): void
    {
        $nextInput = $this->getNextInput();

        $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['childFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'greatGrandParentFolderId' => $nextInput['greatGrandParentFolderId'],
            'grandParentFolderId' => $nextInput['grandParentFolderId'],
            'parentFolderId' => $nextInput['parentFolderId'],
            'childFolderId' => $nextInput['childFolderId'],
        ]);
    }

    /**
     * @When I request to create a folder under Parent
     *
     * @throws \Exception
     */
    public function iRequestToCreateAFolderUnderParent(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'AttemptedFolder',
                'parentId' => $nextInput['parentFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @Given another user has created a folder with name FolderBelongingToAnotherUser
     *
     * @throws \Exception
     */
    public function anotherUserHasCreatedAFolderWithNameFolderBelongingToAnotherUser(): void
    {
        $nextInput = $this->getNextInput();

        $userId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/sign-up',
            [
                'username' => 'AnotherUser',
                'password' => 'AnotherUserTest12345#',
                'primaryEmail' => 'another_user_test@galeas.com',
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

        $folderBelongingToAnotherUserId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'FolderBelongingToAnotherUser',
                'parentId' => null,
            ],
            $this->signInAndObtainSessionToken('another_user_test@galeas.com', 'AnotherUserTest12345#')
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        // keep existing session token, not that of 'anotherUser'
        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'folderBelongingToAnotherUserId' => $folderBelongingToAnotherUserId,
        ]);
    }

    /**
     * @When I request to create a folder under FolderBelongingToAnotherUser
     *
     * @throws \Exception
     */
    public function iRequestToCreateAFolderUnderFolderBelongingToAnotherUser(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'AttemptedFolder',
                'parentId' => $nextInput['folderBelongingToAnotherUserId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @Given I have created and deleted a folder with name DeletedFolder
     *
     * @throws \Exception
     */
    public function iHaveCreatedAndDeletedAFolderWithNameDeletedFolder(): void
    {
        $nextInput = $this->getNextInput();

        $deletedFolderId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'DeletedFolder',
                'parentId' => null,
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $deletedFolderId,
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'deletedFolderId' => $deletedFolderId,
        ]);
    }

    /**
     * @When I request to delete folder DeletedFolder
     *
     * @throws \Exception
     */
    public function iRequestToDeleteFolderDeletedFolder(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['deletedFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to delete folder GreatGrandParent
     *
     * @throws \Exception
     */
    public function iRequestToDeleteFolderGreatGrandParent(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['greatGrandParentFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to delete folder GrandParent
     *
     * @throws \Exception
     */
    public function iRequestToDeleteFolderGrandParent(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['grandParentFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to delete folder Parent
     *
     * @throws \Exception
     */
    public function iRequestToDeleteFolderParent(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['parentFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to delete folder Child
     *
     * @throws \Exception
     */
    public function iRequestToDeleteFolderChild(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['childFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to delete folder FolderBelongingToAnotherUser
     *
     * @throws \Exception
     */
    public function iRequestToDeleteAFolderUnderFolderBelongingToAnotherUser(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['folderBelongingToAnotherUserId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @Given I have created folders with names MovingFolderA and MovingFolderB
     *
     * @throws \Exception
     */
    public function iHaveCreatedFoldersWithNamesMovingFolderAAndMovingFolderB(): void
    {
        $nextInput = $this->getNextInput();

        $movingFolderAId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'MovingFolderA',
                'parentId' => null,
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $movingFolderBId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'MovingFolderB',
                'parentId' => null,
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'movingFolderAId' => $movingFolderAId,
            'movingFolderBId' => $movingFolderBId,
        ]);
    }

    /**
     * @Given I have deleted MovingFolderA
     *
     * @throws \Exception
     */
    public function iHaveDeletedMovingFolderA(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['movingFolderAId'],
            ],
            $nextInput['sessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'movingFolderAId' => $nextInput['movingFolderAId'],
            'movingFolderBId' => $nextInput['movingFolderBId'],
        ]);
    }

    /**
     * @Given I have deleted MovingFolderB
     *
     * @throws \Exception
     */
    public function iHaveDeletedMovingFolderB(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['movingFolderBId'],
            ],
            $nextInput['sessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'movingFolderAId' => $nextInput['movingFolderAId'],
            'movingFolderBId' => $nextInput['movingFolderBId'],
        ]);
    }

    /**
     * @When I request to move MovingFolderA to MovingFolderB
     *
     * @throws \Exception
     */
    public function iRequestToMoveMovingFolderAToMovingFolderB(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/move-folder',
            [
                'folderId' => $nextInput['movingFolderAId'],
                'destinationFolderId' => $nextInput['movingFolderBId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to move MovingFolderA to be a top level folder
     *
     * @throws \Exception
     */
    public function iRequestToMoveMovingFolderAToBeATopLevelFolder(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/move-folder',
            [
                'folderId' => $nextInput['movingFolderAId'],
                'destinationFolderId' => null,
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @Given I have created a folder with name MovingFolderC
     *
     * @throws \Exception
     */
    public function iHaveCreatedAFolderWithNameMovingFolderC(): void
    {
        $nextInput = $this->getNextInput();

        $movingFolderCId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'MovingFolderC',
                'parentId' => null,
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'movingFolderCId' => $movingFolderCId,
            'childFolderId' => $nextInput['childFolderId'],
            'parentFolderId' => $nextInput['parentFolderId'],
            'grandParentFolderId' => $nextInput['grandParentFolderId'],
            'greatGrandParentFolderId' => $nextInput['greatGrandParentFolderId'],
        ]);
    }

    /**
     * @When I request to move MovingFolderC to Child
     *
     * @throws \Exception
     */
    public function iRequestToMoveMovingFolderCToChild(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/move-folder',
            [
                'folderId' => $nextInput['movingFolderCId'],
                'destinationFolderId' => $nextInput['childFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to move GreatGrandParent to Child
     *
     * @throws \Exception
     */
    public function iRequestToMoveGreatGrandParentToChild(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/move-folder',
            [
                'folderId' => $nextInput['greatGrandParentFolderId'],
                'destinationFolderId' => $nextInput['childFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to move GrandParent to Child
     *
     * @throws \Exception
     */
    public function iRequestToMoveGrandParentToChild(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/move-folder',
            [
                'folderId' => $nextInput['grandParentFolderId'],
                'destinationFolderId' => $nextInput['childFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to move Parent to Child
     *
     * @throws \Exception
     */
    public function iRequestToMoveParentToChild(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/move-folder',
            [
                'folderId' => $nextInput['parentFolderId'],
                'destinationFolderId' => $nextInput['childFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to move Child to be a top level folder
     *
     * @throws \Exception
     */
    public function iRequestToMoveChildToBeATopLevelFolder(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/move-folder',
            [
                'folderId' => $nextInput['childFolderId'],
                'destinationFolderId' => null,
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to move Child to GreatGrandParent
     *
     * @throws \Exception
     */
    public function iRequestToMoveChildToGreatGrandParent(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/move-folder',
            [
                'folderId' => $nextInput['childFolderId'],
                'destinationFolderId' => $nextInput['greatGrandParentFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to move Child to GrandParent
     *
     * @throws \Exception
     */
    public function iRequestToMoveChildToGrandParent(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/move-folder',
            [
                'folderId' => $nextInput['childFolderId'],
                'destinationFolderId' => $nextInput['grandParentFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to move Child to Parent
     *
     * @throws \Exception
     */
    public function iRequestToMoveChildToParent(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/move-folder',
            [
                'folderId' => $nextInput['childFolderId'],
                'destinationFolderId' => $nextInput['parentFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @Given I have created a folder with name MovingFolderD
     *
     * @throws \Exception
     */
    public function iHaveCreatedAFolderWithNameMovingFolderD(): void
    {
        $nextInput = $this->getNextInput();

        $movingFolderCId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'MovingFolderD',
                'parentId' => null,
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'movingFolderDId' => $movingFolderCId,
            'folderBelongingToAnotherUserId' => $nextInput['folderBelongingToAnotherUserId'],
        ]);
    }

    /**
     * @When I request to move MovingFolderD to FolderBelongingToAnotherUser
     *
     * @throws \Exception
     */
    public function iRequestToMoveMovingFolderDToFolderBelongingToAnotherUser(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/move-folder',
            [
                'folderId' => $nextInput['movingFolderDId'],
                'destinationFolderId' => $nextInput['folderBelongingToAnotherUserId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to move FolderBelongingToAnotherUser to MovingFolderD
     *
     * @throws \Exception
     */
    public function iRequestToMoveFolderBelongingToAnotherUserDToMovingFolderD(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/move-folder',
            [
                'folderId' => $nextInput['folderBelongingToAnotherUserId'],
                'destinationFolderId' => $nextInput['movingFolderDId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @Given I have created a folder named FolderToBeRenamed
     *
     * @throws \Exception
     */
    public function iHaveCreatedAFolderNamedFolderToBeRenamed(): void
    {
        $nextInput = $this->getNextInput();

        $folderToBeRenamedId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'FolderToBeRenamed',
                'parentId' => null,
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'folderToBeRenamedId' => $folderToBeRenamedId,
        ]);
    }

    /**
     * @When I request to rename FolderToBeRenamed to :name
     *
     * @throws \Exception
     */
    public function iRequestToRenameFolderToBeRenamedTo_(string $name): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/rename-folder',
            [
                'folderId' => $nextInput['folderToBeRenamedId'],
                'name' => $name,
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to rename FolderToBeRenamed without a new name
     *
     * @throws \Exception
     */
    public function iRequestToRenameFolderToBeRenameWithoutANewName(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/rename-folder',
            [
                'folderId' => $nextInput['folderToBeRenamedId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to rename DeletedFolder to DeletedFolderNewName
     *
     * @throws \Exception
     */
    public function iRequestToRenameDeletedFolderToDeletedFolderNewName(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/rename-folder',
            [
                'folderId' => $nextInput['deletedFolderId'],
                'name' => 'DeletedFolderNewName',
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to rename GreatGrandParent to GreatGrandParentRenamed
     *
     * @throws \Exception
     */
    public function iRequestToRenameGreatGrandParentToGreatGrandParentRenamed(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/rename-folder',
            [
                'folderId' => $nextInput['greatGrandParentFolderId'],
                'name' => 'GreatGrandParentRenamed',
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to rename GrandParent to GrandParentRenamed
     *
     * @throws \Exception
     */
    public function iRequestToRenameGrandParentToGrandParentRenamed(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/rename-folder',
            [
                'folderId' => $nextInput['grandParentFolderId'],
                'name' => 'GrandParentRenamed',
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to rename Parent to ParentRenamed
     *
     * @throws \Exception
     */
    public function iRequestToRenameParentToParentRenamed(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/rename-folder',
            [
                'folderId' => $nextInput['parentFolderId'],
                'name' => 'ParentRenamed',
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to rename Child to ChildRenamed
     *
     * @throws \Exception
     */
    public function iRequestToRenameChildToChildRenamed(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/rename-folder',
            [
                'folderId' => $nextInput['childFolderId'],
                'name' => 'ChildRenamed',
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to rename FolderBelongingToAnotherUser to FolderBelongingToAnotherUserRenamed
     *
     * @throws \Exception
     */
    public function iRequestToRenameFolderBelongingToAnotherUsertoFolderBelongingToAnotherUserRenamed(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/rename-folder',
            [
                'folderId' => $nextInput['folderBelongingToAnotherUserId'],
                'name' => 'FolderBelongingToAnotherUserRenamed',
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @Given I have created two folders under my root folder with names Music and Images
     *
     * @throws \Exception
     */
    public function iHaveCreatedTwoFoldersUnderMyRootFolderWithNamesMusicAndImages(): void
    {
        $nextInput = $this->getNextInput();

        $musicId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'Music',
                'parentId' => null,
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $imagesId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'Images',
                'parentId' => null,
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'musicFolderId' => $musicId,
            'imagesFolderId' => $imagesId,
        ]);
    }

    /**
     * @Given I have created two folders under Music with names Jazz and Rock
     *
     * @throws \Exception
     */
    public function iHaveCreatedTwoFoldersUnderMusicWithNamesJazzAndRock(): void
    {
        $nextInput = $this->getNextInput();

        $jazzId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'Jazz',
                'parentId' => $nextInput['musicFolderId'],
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $rockId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'Rock',
                'parentId' => $nextInput['musicFolderId'],
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'musicFolderId' => $nextInput['musicFolderId'],
            'imagesFolderId' => $nextInput['imagesFolderId'],
            'jazzFolderId' => $jazzId,
            'rockFolderId' => $rockId,
        ]);
    }

    /**
     * @When I request to get the contents of Music
     *
     * @throws \Exception
     */
    public function iRequestToGetTheContentsOfMusic(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/get-folder-contents',
            [
                'folderId' => $nextInput['musicFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'musicFolderId' => $nextInput['musicFolderId'],
            'imagesFolderId' => $nextInput['imagesFolderId'],
            'jazzFolderId' => $nextInput['jazzFolderId'],
            'rockFolderId' => $nextInput['rockFolderId'],
            'response' => $response,
        ]);
    }

    /**
     * @Then I should obtain Music without a parent and with children Jazz and Rock
     *
     * @throws \Exception
     */
    public function iShouldObtainMusicWithoutAParentAndWithChildrenJazzAndRock(): void
    {
        $nextInput = $this->getNextInput();
        $response = $this->obtainJsonResponseFromArray($nextInput);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals(
            [
                'folder' => [
                    'id' => $nextInput['musicFolderId'],
                    'name' => 'Music',
                    'parent' => null,
                ],
                'childrenFolders' => [
                    [
                        'id' => $nextInput['jazzFolderId'],
                        'name' => 'Jazz',
                    ],
                    [
                        'id' => $nextInput['rockFolderId'],
                        'name' => 'Rock',
                    ],
                ],
            ],
            $response->getDecodedJsonArrayFromSuccessfulGaleasResponse()
        );
    }

    /**
     * @When I request to get the contents of Images
     *
     * @throws \Exception
     */
    public function iRequestToGetTheContentsOfImages(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/get-folder-contents',
            [
                'folderId' => $nextInput['imagesFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'musicFolderId' => $nextInput['musicFolderId'],
            'imagesFolderId' => $nextInput['imagesFolderId'],
            'jazzFolderId' => $nextInput['jazzFolderId'],
            'rockFolderId' => $nextInput['rockFolderId'],
            'response' => $response,
        ]);
    }

    /**
     * @Then I should obtain Images without a parent and without children
     *
     * @throws \Exception
     */
    public function iShouldObtainImagesWithoutAParentAndWithoutChildren(): void
    {
        $nextInput = $this->getNextInput();
        $response = $this->obtainJsonResponseFromArray($nextInput);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals(
            [
                'folder' => [
                    'id' => $nextInput['imagesFolderId'],
                    'name' => 'Images',
                    'parent' => null,
                ],
                'childrenFolders' => [
                ],
            ],
            $response->getDecodedJsonArrayFromSuccessfulGaleasResponse()
        );
    }

    /**
     * @Given I have created two folders Classic and HeavyMetal under Rock
     *
     * @throws \Exception
     */
    public function iHaveCreatedTwoFoldersClassicAndHeavyMetalUnderRock(): void
    {
        $nextInput = $this->getNextInput();

        $classicId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'Classic',
                'parentId' => $nextInput['rockFolderId'],
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $heavyMetalId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'HeavyMetal',
                'parentId' => $nextInput['rockFolderId'],
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'musicFolderId' => $nextInput['musicFolderId'],
            'imagesFolderId' => $nextInput['imagesFolderId'],
            'jazzFolderId' => $nextInput['jazzFolderId'],
            'rockFolderId' => $nextInput['rockFolderId'],
            'classicFolderId' => $classicId,
            'heavyMetalFolderId' => $heavyMetalId,
        ]);
    }

    /**
     * @When I request to get the contents of Rock
     *
     * @throws \Exception
     */
    public function iRequestToGetTheContentsOfRock(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/get-folder-contents',
            [
                'folderId' => $nextInput['rockFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'musicFolderId' => $nextInput['musicFolderId'],
            'imagesFolderId' => $nextInput['imagesFolderId'],
            'jazzFolderId' => $nextInput['jazzFolderId'],
            'rockFolderId' => $nextInput['rockFolderId'],
            'classicFolderId' => $nextInput['classicFolderId'],
            'heavyMetalFolderId' => $nextInput['heavyMetalFolderId'],
            'response' => $response,
        ]);
    }

    /**
     * @Then I should obtain Rock with parent Music and children Classic and HeavyMetal
     *
     * @throws \Exception
     */
    public function iShouldObtainRockWithParentMusicAndChildrenClassicAndHeavyMetal(): void
    {
        $nextInput = $this->getNextInput();
        $response = $this->obtainJsonResponseFromArray($nextInput);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals(
            [
                'folder' => [
                    'id' => $nextInput['rockFolderId'],
                    'name' => 'Rock',
                    'parent' => $nextInput['musicFolderId'],
                ],
                'childrenFolders' => [
                    [
                        'id' => $nextInput['classicFolderId'],
                        'name' => 'Classic',
                    ],
                    [
                        'id' => $nextInput['heavyMetalFolderId'],
                        'name' => 'HeavyMetal',
                    ],
                ],
            ],
            $response->getDecodedJsonArrayFromSuccessfulGaleasResponse()
        );
    }

    /**
     * @When I request to get the contents of Jazz
     *
     * @throws \Exception
     */
    public function iRequestToGetTheContentsOfJazz(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/get-folder-contents',
            [
                'folderId' => $nextInput['jazzFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'musicFolderId' => $nextInput['musicFolderId'],
            'imagesFolderId' => $nextInput['imagesFolderId'],
            'jazzFolderId' => $nextInput['jazzFolderId'],
            'rockFolderId' => $nextInput['rockFolderId'],
            'response' => $response,
        ]);
    }

    /**
     * @Then I should obtain Jazz with parent Music and without children
     *
     * @throws \Exception
     */
    public function iShouldObtainJazzWithParentMusicAndWithoutChildren(): void
    {
        $nextInput = $this->getNextInput();
        $response = $this->obtainJsonResponseFromArray($nextInput);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals(
            [
                'folder' => [
                    'id' => $nextInput['jazzFolderId'],
                    'name' => 'Jazz',
                    'parent' => $nextInput['musicFolderId'],
                ],
                'childrenFolders' => [],
            ],
            $response->getDecodedJsonArrayFromSuccessfulGaleasResponse()
        );
    }

    /**
     * @Given I have moved Classic under Jazz
     *
     * @throws \Exception
     */
    public function iHaveMovedClassicUnderJazz(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/move-folder',
            [
                'folderId' => $nextInput['classicFolderId'],
                'destinationFolderId' => $nextInput['jazzFolderId'],
            ],
            $nextInput['sessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'musicFolderId' => $nextInput['musicFolderId'],
            'imagesFolderId' => $nextInput['imagesFolderId'],
            'jazzFolderId' => $nextInput['jazzFolderId'],
            'rockFolderId' => $nextInput['rockFolderId'],
            'classicFolderId' => $nextInput['classicFolderId'],
            'heavyMetalFolderId' => $nextInput['heavyMetalFolderId'],
            'sessionToken' => $nextInput['sessionToken'],
        ]);
    }

    /**
     * @Then I should obtain Rock with parent Music and child HeavyMetal
     *
     * @throws \Exception
     */
    public function iShouldObtainRockWithParentMusicAndChildHeavyMetal(): void
    {
        $nextInput = $this->getNextInput();
        $response = $this->obtainJsonResponseFromArray($nextInput);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals(
            [
                'folder' => [
                    'id' => $nextInput['rockFolderId'],
                    'name' => 'Rock',
                    'parent' => $nextInput['musicFolderId'],
                ],
                'childrenFolders' => [
                    [
                        'id' => $nextInput['heavyMetalFolderId'],
                        'name' => 'HeavyMetal',
                    ],
                ],
            ],
            $response->getDecodedJsonArrayFromSuccessfulGaleasResponse()
        );
    }

    /**
     * @When I request to get the contents of Classic
     *
     * @throws \Exception
     */
    public function iRequestToGetTheContentsOfClassic(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/get-folder-contents',
            [
                'folderId' => $nextInput['classicFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'musicFolderId' => $nextInput['musicFolderId'],
            'imagesFolderId' => $nextInput['imagesFolderId'],
            'jazzFolderId' => $nextInput['jazzFolderId'],
            'rockFolderId' => $nextInput['rockFolderId'],
            'classicFolderId' => $nextInput['classicFolderId'],
            'heavyMetalFolderId' => $nextInput['heavyMetalFolderId'],
            'response' => $response,
        ]);
    }

    /**
     * @Then I should obtain Classic with parent Jazz and without children
     *
     * @throws \Exception
     */
    public function iShouldObtainClassicWithParentJazzAndWithoutChildren(): void
    {
        $nextInput = $this->getNextInput();
        $response = $this->obtainJsonResponseFromArray($nextInput);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals(
            [
                'folder' => [
                    'id' => $nextInput['classicFolderId'],
                    'name' => 'Classic',
                    'parent' => $nextInput['jazzFolderId'],
                ],
                'childrenFolders' => [],
            ],
            $response->getDecodedJsonArrayFromSuccessfulGaleasResponse()
        );
    }

    /**
     * @Given I have deleted Classic
     *
     * @throws \Exception
     */
    public function iHaveDeletedClassic(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['classicFolderId'],
            ],
            $nextInput['sessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'musicFolderId' => $nextInput['musicFolderId'],
            'imagesFolderId' => $nextInput['imagesFolderId'],
            'jazzFolderId' => $nextInput['jazzFolderId'],
            'rockFolderId' => $nextInput['rockFolderId'],
            'classicFolderId' => $nextInput['classicFolderId'],
            'heavyMetalFolderId' => $nextInput['heavyMetalFolderId'],
            'sessionToken' => $nextInput['sessionToken'],
        ]);
    }

    /**
     * @Given I have deleted Music
     *
     * @throws \Exception
     */
    public function iHaveDeletedMusic(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['musicFolderId'],
            ],
            $nextInput['sessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'musicFolderId' => $nextInput['musicFolderId'],
            'imagesFolderId' => $nextInput['imagesFolderId'],
            'jazzFolderId' => $nextInput['jazzFolderId'],
            'rockFolderId' => $nextInput['rockFolderId'],
            'sessionToken' => $nextInput['sessionToken'],
        ]);
    }

    /**
     * @Given I have created a folder with name Space under Images
     *
     * @throws \Exception
     */
    public function iHaveCreatedAFolderWithNameSpaceUnderImages(): void
    {
        $nextInput = $this->getNextInput();

        $spaceId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'Space',
                'parentId' => $nextInput['imagesFolderId'],
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'musicFolderId' => $nextInput['musicFolderId'],
            'imagesFolderId' => $nextInput['imagesFolderId'],
            'spaceFolderId' => $spaceId,
            'jazzFolderId' => $nextInput['jazzFolderId'],
            'rockFolderId' => $nextInput['rockFolderId'],
        ]);
    }

    /**
     * @Given I have renamed Space to Astronomy
     *
     * @throws \Exception
     */
    public function iHaveRenamedSpaceToAstronomy(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/rename-folder',
            [
                'folderId' => $nextInput['spaceFolderId'],
                'name' => 'Astronomy',
            ],
            $nextInput['sessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'musicFolderId' => $nextInput['musicFolderId'],
            'imagesFolderId' => $nextInput['imagesFolderId'],
            'astronomyFolderId' => $nextInput['spaceFolderId'],
            'jazzFolderId' => $nextInput['jazzFolderId'],
            'rockFolderId' => $nextInput['rockFolderId'],
        ]);
    }

    /**
     * @When I request to get the contents of Astronomy
     *
     * @throws \Exception
     */
    public function iRequestToGetTheContentsOfAstronomy(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/get-folder-contents',
            [
                'folderId' => $nextInput['astronomyFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'musicFolderId' => $nextInput['musicFolderId'],
            'imagesFolderId' => $nextInput['imagesFolderId'],
            'astronomyFolderId' => $nextInput['astronomyFolderId'],
            'jazzFolderId' => $nextInput['jazzFolderId'],
            'rockFolderId' => $nextInput['rockFolderId'],
            'response' => $response,
        ]);
    }

    /**
     * @When I request to get the contents of Images, with renamed folder Space
     *
     * @throws \Exception
     */
    public function iRequestToGetTheContentsOfImagesWithRenamedFolderSpace(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/get-folder-contents',
            [
                'folderId' => $nextInput['imagesFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'musicFolderId' => $nextInput['musicFolderId'],
            'imagesFolderId' => $nextInput['imagesFolderId'],
            'astronomyFolderId' => $nextInput['astronomyFolderId'],
            'jazzFolderId' => $nextInput['jazzFolderId'],
            'rockFolderId' => $nextInput['rockFolderId'],
            'response' => $response,
        ]);
    }

    /**
     * @Then I should obtain Astronomy with its updated name
     *
     * @throws \Exception
     */
    public function iShouldObtainAstronomyWithItsUpdatedName(): void
    {
        $nextInput = $this->getNextInput();
        $response = $this->obtainJsonResponseFromArray($nextInput);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals(
            [
                'folder' => [
                    'id' => $nextInput['astronomyFolderId'],
                    'name' => 'Astronomy',
                    'parent' => $nextInput['imagesFolderId'],
                ],
                'childrenFolders' => [],
            ],
            $response->getDecodedJsonArrayFromSuccessfulGaleasResponse()
        );
    }

    /**
     * @Then I should obtain Images without a parent and with child Astronomy
     *
     * @throws \Exception
     */
    public function iShouldObtainImagesWithoutAParentAndWithChildAstronomy(): void
    {
        $nextInput = $this->getNextInput();
        $response = $this->obtainJsonResponseFromArray($nextInput);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals(
            [
                'folder' => [
                    'id' => $nextInput['imagesFolderId'],
                    'name' => 'Images',
                    'parent' => null,
                ],
                'childrenFolders' => [
                    [
                        'id' => $nextInput['astronomyFolderId'],
                        'name' => 'Astronomy',
                    ],
                ],
            ],
            $response->getDecodedJsonArrayFromSuccessfulGaleasResponse()
        );
    }

    /**
     * @Given another user has created a folder with name MusicBelongingToAnotherUser
     *
     * @throws \Exception
     */
    public function anotherUserHasCreatedAFolderWithNameMusicBelongingToAnotherUser(): void
    {
        $nextInput = $this->getNextInput();

        $userId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/identity/user/sign-up',
            [
                'username' => 'AnotherUser',
                'password' => 'AnotherUserTest12345#',
                'primaryEmail' => 'another_user_test@galeas.com',
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

        $musicBelongingToAnotherUserFolderId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'MusicBelongingToAnotherUser',
                'parentId' => null,
            ],
            $this->signInAndObtainSessionToken('another_user_test@galeas.com', 'AnotherUserTest12345#')
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        // keep existing session token, not that of 'anotherUser'
        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'musicBelongingToAnotherUserFolderId' => $musicBelongingToAnotherUserFolderId,
        ]);
    }

    /**
     * @When I request to get the contents of MusicBelongingToAnotherUser
     *
     * @throws \Exception
     */
    public function iRequestToGetTheContentsOfMusicBelongingToAnotherUser(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/get-folder-contents',
            [
                'folderId' => $nextInput['musicBelongingToAnotherUserFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @Given I have created two folders under my root folder with names Art and Science
     *
     * @throws \Exception
     */
    public function iHaveCreatedTwoFoldersUnderMyRootFolderWithNamesArtAndScience(): void
    {
        $nextInput = $this->getNextInput();

        $artId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'Art',
                'parentId' => null,
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $scienceId = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/create-folder',
            [
                'name' => 'Science',
                'parentId' => null,
            ],
            $nextInput['sessionToken']
        )->getDecodedJsonFromSuccessfulGaleasResponse()->folderId;

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'artFolderId' => $artId,
            'scienceFolderId' => $scienceId,
        ]);
    }

    /**
     * @When I request to get the contents of my root folder
     *
     * @throws \Exception
     */
    public function iRequestToGetTheContentsOfMyRootFolder(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/get-root-folder-contents',
            [],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'artFolderId' => $nextInput['artFolderId'],
            'scienceFolderId' => $nextInput['scienceFolderId'],
            'response' => $response,
        ]);
    }

    /**
     * @Then I should obtain my root folder with children Art and Science
     *
     * @throws \Exception
     */
    public function iShouldObtainMyRootFolderWithChildrenArtAndScience(): void
    {
        $nextInput = $this->getNextInput();
        $response = $this->obtainJsonResponseFromArray($nextInput);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals(
            [
                'childrenFolders' => [
                    [
                        'id' => $nextInput['artFolderId'],
                        'name' => 'Art',
                    ],
                    [
                        'id' => $nextInput['scienceFolderId'],
                        'name' => 'Science',
                    ],
                ],
            ],
            $response->getDecodedJsonArrayFromSuccessfulGaleasResponse()
        );
    }

    /**
     * @When I request to get the contents of my empty root folder
     *
     * @throws \Exception
     */
    public function iRequestToGetTheContentsOfMyEmptyRootFolder(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/get-root-folder-contents',
            [],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @Then I should obtain my root folder without children
     *
     * @throws \Exception
     */
    public function iShouldObtainMyRootFolderWithoutChildren(): void
    {
        $nextInput = $this->getNextInput();
        $response = $this->obtainJsonResponseFromArray($nextInput);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals(
            [
                'childrenFolders' => [
                ],
            ],
            $response->getDecodedJsonArrayFromSuccessfulGaleasResponse()
        );
    }

    /**
     * @Given I have renamed Science to ScienceSubject
     *
     * @throws \Exception
     */
    public function iHaveRenamedScienceToScienceSubject(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/rename-folder',
            [
                'folderId' => $nextInput['scienceFolderId'],
                'name' => 'ScienceSubject',
            ],
            $nextInput['sessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'artFolderId' => $nextInput['artFolderId'],
            'scienceSubjectFolderId' => $nextInput['scienceFolderId'],
        ]);
    }

    /**
     * @When I request to get the contents of my root folder, with renamed folder Science
     *
     * @throws \Exception
     */
    public function iRequestToGetTheContentsOfMyRootFolderWithRenamedFolderScience(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/get-root-folder-contents',
            [],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'artFolderId' => $nextInput['artFolderId'],
            'scienceSubjectFolderId' => $nextInput['scienceSubjectFolderId'],
            'response' => $response,
        ]);
    }

    /**
     * @Then I should obtain my root folder with children Art and ScienceSubject
     *
     * @throws \Exception
     */
    public function iShouldObtainMyRootFolderWithChildrenArtAndScienceSubject(): void
    {
        $nextInput = $this->getNextInput();
        $response = $this->obtainJsonResponseFromArray($nextInput);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals(
            [
                'childrenFolders' => [
                    [
                        'id' => $nextInput['artFolderId'],
                        'name' => 'Art',
                    ],
                    [
                        'id' => $nextInput['scienceSubjectFolderId'],
                        'name' => 'ScienceSubject',
                    ],
                ],
            ],
            $response->getDecodedJsonArrayFromSuccessfulGaleasResponse()
        );
    }

    /**
     * @Given I have moved Science under Art
     *
     * @throws \Exception
     */
    public function iHaveMovedArtUnderScience(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/move-folder',
            [
                'folderId' => $nextInput['scienceFolderId'],
                'destinationFolderId' => $nextInput['artFolderId'],
            ],
            $nextInput['sessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'artFolderId' => $nextInput['artFolderId'],
            'scienceFolderId' => $nextInput['scienceFolderId'],
        ]);
    }

    /**
     * @Then I should obtain my root folder with child Art
     *
     * @throws \Exception
     */
    public function iShouldObtainMyRootFolderWithChildArt(): void
    {
        $nextInput = $this->getNextInput();
        $response = $this->obtainJsonResponseFromArray($nextInput);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals(
            [
                'childrenFolders' => [
                    [
                        'id' => $nextInput['artFolderId'],
                        'name' => 'Art',
                    ],
                ],
            ],
            $response->getDecodedJsonArrayFromSuccessfulGaleasResponse()
        );
    }

    /**
     * @Given I have deleted Science
     *
     * @throws \Exception
     */
    public function iHaveDeletedScience(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/folder/delete-folder',
            [
                'folderId' => $nextInput['scienceFolderId'],
            ],
            $nextInput['sessionToken']
        );

        Assert::assertEquals(200, $response->getStatusCode());

        $this->setNextInput([
            'sessionToken' => $nextInput['sessionToken'],
            'artFolderId' => $nextInput['artFolderId'],
            'scienceFolderId' => $nextInput['scienceFolderId'],
        ]);
    }

    /**
     * @When I request to log that I have opened folder DeletedFolder
     *
     * @throws \Exception
     */
    public function iRequestToLogThatIHaveOpenedFolderDeletedFolder(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/logged-folder-opened/log-folder-opened',
            [
                'folderId' => $nextInput['deletedFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to log that I have opened folder GreatGrandParent
     *
     * @throws \Exception
     */
    public function iRequestToLogThatIHaveOpenedFolderGreatGrandParent(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/logged-folder-opened/log-folder-opened',
            [
                'folderId' => $nextInput['greatGrandParentFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to log that I have opened folder GrandParent
     *
     * @throws \Exception
     */
    public function iRequestToLogThatIHaveOpenedFolderGrandParent(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/logged-folder-opened/log-folder-opened',
            [
                'folderId' => $nextInput['grandParentFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to log that I have opened folder Parent
     *
     * @throws \Exception
     */
    public function iRequestToLogThatIHaveOpenedFolderParent(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/logged-folder-opened/log-folder-opened',
            [
                'folderId' => $nextInput['parentFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to log that I have opened folder Child
     *
     * @throws \Exception
     */
    public function iRequestToLogThatIHaveOpenedFolderChild(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/logged-folder-opened/log-folder-opened',
            [
                'folderId' => $nextInput['childFolderId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to log that I have opened folder FolderBelongingToAnotherUser
     *
     * @throws \Exception
     */
    public function iRequestToLogThatIHaveOpenedFolderFolderBelongingToAnotherUser(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/logged-folder-opened/log-folder-opened',
            [
                'folderId' => $nextInput['folderBelongingToAnotherUserId'],
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
    }

    /**
     * @When I request to log that I have opened my root folder
     *
     * @throws \Exception
     */
    public function iRequestToLogThatIHaveOpenedMyRootFolder(): void
    {
        $nextInput = $this->getNextInput();

        $response = $this->makeJsonPostRequestAndGetResponse(
            'api/v1/library/logged-root-folder-opened/log-root-folder-opened',
            [
            ],
            $nextInput['sessionToken']
        );

        $this->setNextInput([
            'response' => $response,
        ]);
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
            case 'the created folder\'s name is required':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        ['[name] The property name is required'],
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
            case 'the created folder\'s name is invalid':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Library_Folder_CreateFolder_InvalidFolderName',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'the parent of the created folder is deleted':
            case 'I don\'t own the parent FolderBelongingToAnotherUser':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Library_Folder_CreateFolder_ParentFolderNotOwned',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'DeletedFolder is already deleted':
            case 'the folder was already deleted':
            case 'I don\'t own the folder to be deleted, FolderBelongingToAnotherUser':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Library_Folder_DeleteFolder_FolderNotOwned',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'the destination folder is deleted':
            case 'I don\'t own the destination folder':
            Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Library_Folder_MoveFolder_DestinationFolderNotOwned',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'the moved folder is deleted':
            case 'I don\'t own the moved folder':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Library_Folder_MoveFolder_FolderNotOwned',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'the moved folder is an ancestor of the destination folder':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Library_Folder_MoveFolder_FolderIsAncestorOfDestinationFolder',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );
                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'the renamed folder\'s name is invalid':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Library_Folder_RenameFolder_InvalidFolderName',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    400,
                    $response->getStatusCode()
                );
                break;
            case 'the renamed folder\'s name is required':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        ['[name] The property name is required'],
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
            case 'the renamed folder is deleted':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Library_Folder_RenameFolder_FolderNotOwned',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'the contents of this folder are deleted':
            case 'the contents of this folder are not mine':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Library_Folder_GetFolderContents_FolderNotOwned',
                        ''
                    ),
                    $response->getDecodedJsonAsGaleasErrorResponse()
                );

                Assert::assertEquals(
                    403,
                    $response->getStatusCode()
                );
                break;
            case 'the opened folder is deleted':
            case 'I don\'t own the opened folder FolderBelongingToAnotherUser':
                Assert::assertEquals(
                    GaleasErrorResponse::fromParameters(
                        [],
                        'Library_LoggedFolderOpened_LogFolderOpened_FolderNotOwned',
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
