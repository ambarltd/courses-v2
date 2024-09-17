<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\UserWithEmail;

use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\RequestedChange;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\Unverified;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\UserIdFromSignInEmail;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\UserWithEmail;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\Verified;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class UserIdFromSignInEmailTest extends KernelTestBase
{
    public function testUnverified(): void
    {
        $userIdFromEmailService = $this->getContainer()
            ->get(UserIdFromSignInEmail::class);

        Assert::assertNull($userIdFromEmailService->userIdFromSignInEmail('deF@galeas.com'));
        Assert::assertNull($userIdFromEmailService->userIdFromSignInEmail('xyZ@galeas.com'));

        $this->getProjectionDocumentManager()->persist(
            UserWithEmail::fromUserIdAndEmails(
                'user_id_1',
                'willBeOverridenA@galeas.com',
                'willBeOverridenB@galeas.com',
                Unverified::setStatus()
            )
        );
        $this->getProjectionDocumentManager()->persist(
            UserWithEmail::fromUserIdAndEmails(
                'user_id_1',
                'aBc@galeas.com',
                'dEf@galeas.com',
                Unverified::setStatus()
            )
        );
        $this->getProjectionDocumentManager()->persist(
            UserWithEmail::fromUserIdAndEmails(
                'user_id_2',
                'uVw@galeas.com',
                'xYz@galeas.com',
                Unverified::setStatus()
            )
        );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(null, $userIdFromEmailService->userIdFromSignInEmail('willBeOverridenA@galeas.com'));
        Assert::assertEquals(null, $userIdFromEmailService->userIdFromSignInEmail('willBeOverridenB@galeas.com'));
        Assert::assertEquals(null, $userIdFromEmailService->userIdFromSignInEmail('aBc@galeas.com'));
        Assert::assertEquals('user_id_1', $userIdFromEmailService->userIdFromSignInEmail('deF@galeas.com'));
        Assert::assertEquals(null, $userIdFromEmailService->userIdFromSignInEmail('uVw@galeas.com'));
        Assert::assertEquals('user_id_2', $userIdFromEmailService->userIdFromSignInEmail('xyZ@galeas.com'));
    }

    public function testVerified(): void
    {
        $userIdFromEmailService = $this->getContainer()
            ->get(UserIdFromSignInEmail::class);

        Assert::assertNull($userIdFromEmailService->userIdFromSignInEmail('aBc@galeas.com'));
        Assert::assertNull($userIdFromEmailService->userIdFromSignInEmail('uVw@galeas.com'));

        $this->getProjectionDocumentManager()->persist(
            UserWithEmail::fromUserIdAndEmails(
                'user_id_1',
                'willBeOverridenA@galeas.com',
                'willBeOverridenB@galeas.com',
                Verified::setStatus()
            )
        );
        $this->getProjectionDocumentManager()->persist(
            UserWithEmail::fromUserIdAndEmails(
                'user_id_1',
                'aBc@galeas.com',
                'dEf@galeas.com',
                Verified::setStatus()
            )
        );
        $this->getProjectionDocumentManager()->persist(
            UserWithEmail::fromUserIdAndEmails(
                'user_id_2',
                'uVw@galeas.com',
                'xYZ@galeas.com',
                Verified::setStatus()
            )
        );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(null, $userIdFromEmailService->userIdFromSignInEmail('willBeOverridenA@galeas.com'));
        Assert::assertEquals(null, $userIdFromEmailService->userIdFromSignInEmail('willBeOverridenB@galeas.com'));
        Assert::assertEquals('user_id_1', $userIdFromEmailService->userIdFromSignInEmail('abC@galeas.com'));
        Assert::assertEquals(null, $userIdFromEmailService->userIdFromSignInEmail('dEf@galeas.com'));
        Assert::assertEquals('user_id_2', $userIdFromEmailService->userIdFromSignInEmail('uvW@galeas.com'));
        Assert::assertEquals(null, $userIdFromEmailService->userIdFromSignInEmail('xYz@galeas.com'));
    }

    public function testRequestedChange(): void
    {
        $userIdFromEmailService = $this->getContainer()
            ->get(UserIdFromSignInEmail::class);

        Assert::assertNull($userIdFromEmailService->userIdFromSignInEmail('aBc@galeas.com'));
        Assert::assertNull($userIdFromEmailService->userIdFromSignInEmail('uVw@galeas.com'));

        $this->getProjectionDocumentManager()->persist(
            UserWithEmail::fromUserIdAndEmails(
                'user_id_1',
                'willBeOverridenA@galeas.com',
                'willBeOverridenB@galeas.com',
                RequestedChange::setStatus()
            )
        );
        $this->getProjectionDocumentManager()->persist(
            UserWithEmail::fromUserIdAndEmails(
                'user_id_1',
                'aBc@galeas.com',
                'dEf@galeas.com',
                RequestedChange::setStatus()
            )
        );
        $this->getProjectionDocumentManager()->persist(
            UserWithEmail::fromUserIdAndEmails(
                'user_id_2',
                'uVw@galeas.com',
                'xYZ@galeas.com',
                RequestedChange::setStatus()
            )
        );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(null, $userIdFromEmailService->userIdFromSignInEmail('willBeOverridenA@galeas.com'));
        Assert::assertEquals(null, $userIdFromEmailService->userIdFromSignInEmail('willBeOverridenB@galeas.com'));
        Assert::assertEquals('user_id_1', $userIdFromEmailService->userIdFromSignInEmail('abC@galeas.com'));
        Assert::assertEquals(null, $userIdFromEmailService->userIdFromSignInEmail('dEf@galeas.com'));
        Assert::assertEquals('user_id_2', $userIdFromEmailService->userIdFromSignInEmail('uvW@galeas.com'));
        Assert::assertEquals(null, $userIdFromEmailService->userIdFromSignInEmail('xYz@galeas.com'));
    }
}
