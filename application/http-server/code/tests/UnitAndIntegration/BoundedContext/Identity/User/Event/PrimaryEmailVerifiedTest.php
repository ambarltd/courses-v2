<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Event;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\AccountDetails;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\HashedPassword;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\RequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerificationCode;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class PrimaryEmailVerifiedTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();

        $primaryEmailVerified = PrimaryEmailVerified::fromProperties(
            $aggregateId,
            $authorizerId,
            [1, 2, 3],
            'code1234'
        );

        Assert::assertInstanceOf(
            Id::class,
            $primaryEmailVerified->eventId()
        );
        Assert::assertNotEquals(
            $primaryEmailVerified->eventId(),
            $primaryEmailVerified->aggregateId()
        );
        Assert::assertNotEquals(
            $primaryEmailVerified->eventId(),
            $primaryEmailVerified->authorizerId()
        );
        Assert::assertEquals(
            $aggregateId,
            $primaryEmailVerified->aggregateId()
        );
        Assert::assertEquals(
            $authorizerId,
            $primaryEmailVerified->authorizerId()
        );
        Assert::assertEquals(
            null,
            $primaryEmailVerified->sourceEventId()
        );
        Assert::assertEquals(
            [1, 2, 3],
            $primaryEmailVerified->eventMetadata()
        );
        Assert::assertEquals(
            'code1234',
            $primaryEmailVerified->verifiedWithCode()
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

        $user = User::fromProperties(
            $aggregateId,
            UnverifiedEmail::fromEmailAndVerificationCode(
                Email::fromEmail(
                    'test@example.com'
                ),
                VerificationCode::fromVerificationCode(
                    'some_verification_code'
                )
            ),
            HashedPassword::fromHash(
                '1234abcdef'
            ),
            AccountDetails::fromDetails(
                'username',
                true
            )
        );

        $transformedUser = PrimaryEmailVerified::fromProperties(
            $aggregateId,
            $authorizerId,
            [],
            'changed_code_1234'
        )->transformUser($user);

        if (!($transformedUser->primaryEmailStatus() instanceof VerifiedEmail)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            'test@example.com',
            $transformedUser
                ->primaryEmailStatus()
                ->email()
                ->email()
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
    public function testTransformVerified(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();

        $user = User::fromProperties(
            $aggregateId,
            VerifiedEmail::fromEmail(
                Email::fromEmail(
                    'test@example.com'
                )
            ),
            HashedPassword::fromHash(
                '1234abcdef'
            ),
            AccountDetails::fromDetails(
                'username',
                true
            )
        );

        $transformedUser = PrimaryEmailVerified::fromProperties(
            $aggregateId,
            $authorizerId,
            [],
            'changed_code_1234'
        )->transformUser($user);

        if (!($transformedUser->primaryEmailStatus() instanceof VerifiedEmail)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            'test@example.com',
            $transformedUser
                ->primaryEmailStatus()
                ->email()
                ->email()
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
    public function testTransformRequestedChange(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();

        $user = User::fromProperties(
            $aggregateId,
            RequestedNewEmail::fromEmailsAndVerificationCode(
                Email::fromEmail(
                    'test@example.com'
                ),
                Email::fromEmail(
                    'requested@example.com'
                ),
                VerificationCode::fromVerificationCode(
                    'test@example.com'
                )
            ),
            HashedPassword::fromHash(
                '1234abcdef'
            ),
            AccountDetails::fromDetails(
                'username',
                true
            )
        );

        $transformedUser = PrimaryEmailVerified::fromProperties(
            $aggregateId,
            $authorizerId,
            [],
            'changed_code_1234'
        )->transformUser($user);

        if (!($transformedUser->primaryEmailStatus() instanceof VerifiedEmail)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            'requested@example.com',
            $transformedUser
                ->primaryEmailStatus()
                ->email()
                ->email()
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
