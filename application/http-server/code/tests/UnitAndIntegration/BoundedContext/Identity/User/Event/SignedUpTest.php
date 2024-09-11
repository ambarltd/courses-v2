<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Event;

use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Primitive\PrimitiveValidation\Email\EmailVerificationCodeValidator;
use Galeas\Api\Primitive\PrimitiveValidation\Security\BCryptHashValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class SignedUpTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $signedUp = SignedUp::fromProperties(
            [1, 2, 3],
            'test@example.com',
            'my_password',
            'username',
            true
        );

        Assert::assertInstanceOf(
            Id::class,
            $signedUp->eventId()
        );
        Assert::assertInstanceOf(
            Id::class,
            $signedUp->aggregateId()
        );
        Assert::assertNotEquals(
            $signedUp->eventId(),
            $signedUp->aggregateId()
        );
        Assert::assertInstanceOf(
            Id::class,
            $signedUp->authorizerId()
        );
        Assert::assertEquals(
            $signedUp->aggregateId(),
            $signedUp->authorizerId()
        );
        Assert::assertEquals(
            null,
            $signedUp->sourceEventId()
        );
        Assert::assertEquals(
            [1, 2, 3],
            $signedUp->eventMetadata()
        );
        Assert::assertEquals(
            'test@example.com',
            $signedUp->primaryEmail()
        );
        Assert::assertTrue(
            EmailVerificationCodeValidator::isValid($signedUp->primaryEmailVerificationCode()),
            'Verification code is not valid'
        );
        Assert::assertEquals(
            'username',
            $signedUp->username()
        );
        Assert::assertEquals(
            true,
            $signedUp->termsOfUseAccepted()
        );
        Assert::assertTrue(
            BCryptHashValidator::isValid(
                $signedUp->hashedPassword()
            ),
            'Invalid bcrypt hash '.$signedUp->hashedPassword()
        );
        Assert::assertTrue(
            password_verify(
                'my_password',
                $signedUp->hashedPassword()
            ),
            'Password hash does not match'
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testCreateAggregate(): void
    {
        $user = SignedUp::fromProperties(
            [],
            'test@example.com',
            'my_password',
            'username',
            true
        )->createUser();

        Assert::assertInstanceOf(
            Id::class,
            $user->id()
        );

        if (!($user->primaryEmailStatus() instanceof UnverifiedEmail)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            'test@example.com',
            $user
                ->primaryEmailStatus()
                ->email()
                ->email()
        );
        Assert::assertTrue(
            EmailVerificationCodeValidator::isValid(
                $user
                    ->primaryEmailStatus()
                    ->verificationCode()
                    ->verificationCode()
            ),
            'Verification code is not valid.'
        );
        Assert::assertEquals(
            'username',
            $user
                ->accountDetails()
                ->username()
        );
        Assert::assertEquals(
            true,
            $user
                ->accountDetails()
                ->termsOfUseAccepted()
        );
        Assert::assertTrue(
            password_verify(
                'my_password',
                $user
                    ->hashedPassword()
                    ->hash()
            ),
            'Password hash does not match'
        );
    }
}
