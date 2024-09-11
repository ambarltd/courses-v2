<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Event;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\AccountDetails;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\HashedPassword;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\RequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerificationCode;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Primitive\PrimitiveCreation\Email\EmailVerificationCodeCreator;
use Galeas\Api\Primitive\PrimitiveValidation\Email\EmailVerificationCodeValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class PrimaryEmailChangeRequestedTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();

        $primaryEmailChangeRequested = PrimaryEmailChangeRequested::fromProperties(
            $aggregateId,
            $authorizerId,
            [1, 2, 3],
            'new@example.com',
        'hashedPassword'
        );

        Assert::assertInstanceOf(
            Id::class,
            $primaryEmailChangeRequested->eventId()
        );
        Assert::assertNotContains(
            $primaryEmailChangeRequested->eventId(),
            [
                $primaryEmailChangeRequested->aggregateId(),
                $primaryEmailChangeRequested->authorizerId(),
            ]
        );
        Assert::assertEquals(
            $aggregateId,
            $primaryEmailChangeRequested->aggregateId()
        );
        Assert::assertEquals(
            $authorizerId,
            $primaryEmailChangeRequested->authorizerId()
        );
        Assert::assertEquals(
            null,
            $primaryEmailChangeRequested->sourceEventId()
        );
        Assert::assertEquals(
            [1, 2, 3],
            $primaryEmailChangeRequested->eventMetadata()
        );
        Assert::assertEquals(
            'new@example.com',
            $primaryEmailChangeRequested->newEmailRequested()
        );
        Assert::assertEquals(
            'hashedPassword',
            $primaryEmailChangeRequested->requestedWithHashedPassword()
        );
        Assert::assertTrue(
            EmailVerificationCodeValidator::isValid(
                $primaryEmailChangeRequested->newVerificationCode()
            ),
            'Verification code is not valid.'
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testTransformVerified(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();

        $user = User::fromProperties(
            $aggregateId,
            VerifiedEmail::fromEmail(
                Email::fromEmail(
                    'verified@example.com'
                )
            ),
            HashedPassword::fromHash(
                'abcdef1234560'
            ),
            AccountDetails::fromDetails(
                'username_1',
                true
            )
        );

        $transformedUser = PrimaryEmailChangeRequested::fromProperties(
            $aggregateId,
            $authorizerId,
            [],
            'new@example.com',
            'some_hashed_password'
        )->transformUser($user);

        if (!($transformedUser->primaryEmailStatus() instanceof RequestedNewEmail)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            'verified@example.com',
            $transformedUser
                ->primaryEmailStatus()
                ->verifiedEmail()
                ->email()
        );
        Assert::assertEquals(
            'new@example.com',
            $transformedUser
                ->primaryEmailStatus()
                ->requestedEmail()
                ->email()
        );
        Assert::assertTrue(
            EmailVerificationCodeValidator::isValid(
                $transformedUser
                    ->primaryEmailStatus()
                    ->verificationCode()
                    ->verificationCode()
            ),
            'Verification code is not valid.'
        );

        Assert::assertEquals(
            $user->id(),
            $transformedUser->id()
        );
        Assert::assertEquals(
            $user->hashedPassword(),
            $transformedUser->hashedPassword()
        );
        Assert::assertEquals(
            $user->accountDetails(),
            $transformedUser->accountDetails()
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testTransformChangeRequested(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();

        $user = User::fromProperties(
            $aggregateId,
            RequestedNewEmail::fromEmailsAndVerificationCode(
                Email::fromEmail(
                    'verified@example.com'
                ),
                Email::fromEmail(
                    'previous_new@example.com'
                ),
                VerificationCode::fromVerificationCode(
                    'verification_code'
                )
            ),
            HashedPassword::fromHash(
                'abcdef1234560'
            ),
            AccountDetails::fromDetails(
                'username_1',
                true
            )
        );

        $transformedUser = PrimaryEmailChangeRequested::fromProperties(
            $aggregateId,
            $authorizerId,
            [],
            'new@example.com',
            'some_hashed_password'
        )->transformUser($user);

        if (!($transformedUser->primaryEmailStatus() instanceof RequestedNewEmail)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            'verified@example.com',
            $transformedUser
                ->primaryEmailStatus()
                ->verifiedEmail()
                ->email()
        );
        Assert::assertEquals(
            'new@example.com',
            $transformedUser
                ->primaryEmailStatus()
                ->requestedEmail()
                ->email()
        );
        Assert::assertTrue(
            EmailVerificationCodeValidator::isValid(
                $transformedUser
                    ->primaryEmailStatus()
                    ->verificationCode()
                    ->verificationCode()
            ),
            'Verification code is not valid.'
        );

        Assert::assertEquals(
            $user->id(),
            $transformedUser->id()
        );
        Assert::assertEquals(
            $user->hashedPassword(),
            $transformedUser->hashedPassword()
        );
        Assert::assertEquals(
            $user->accountDetails(),
            $transformedUser->accountDetails()
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testTransformUnverified(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();
        $oldVerificationCode = EmailVerificationCodeCreator::create();

        $user = User::fromProperties(
            $aggregateId,
            UnverifiedEmail::fromEmailAndVerificationCode(
                Email::fromEmail(
                    'unverified@example.com'
                ),
                VerificationCode::fromVerificationCode(
                    EmailVerificationCodeCreator::create()
                )
            ),
            HashedPassword::fromHash(
                'abcdef1234560'
            ),
            AccountDetails::fromDetails(
                'username_1',
                true
            )
        );

        $transformedUser = PrimaryEmailChangeRequested::fromProperties(
            $aggregateId,
            $authorizerId,
            [],
            'new@example.com',
            'some_hashed_password'
        )->transformUser($user);

        if (!($transformedUser->primaryEmailStatus() instanceof UnverifiedEmail)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            'new@example.com',
            $transformedUser
                ->primaryEmailStatus()
                ->email()
                ->email()
        );
        Assert::assertTrue(
            EmailVerificationCodeValidator::isValid(
                $transformedUser
                    ->primaryEmailStatus()
                    ->verificationCode()
                    ->verificationCode()
            ),
            'Verification code is not valid.'
        );
        Assert::assertNotEquals(
            $oldVerificationCode,
            $transformedUser
                ->primaryEmailStatus()
                ->verificationCode()
                ->verificationCode()
        );

        Assert::assertEquals(
            $user->id(),
            $transformedUser->id()
        );
        Assert::assertEquals(
            $user->hashedPassword(),
            $transformedUser->hashedPassword()
        );
        Assert::assertEquals(
            $user->accountDetails(),
            $transformedUser->accountDetails()
        );
    }
}
