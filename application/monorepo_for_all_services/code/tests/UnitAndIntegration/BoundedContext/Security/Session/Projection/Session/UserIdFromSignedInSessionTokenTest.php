<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\Session;

use Galeas\Api\BoundedContext\Security\Session\Projection\Session\Session;
use Galeas\Api\BoundedContext\Security\Session\Projection\Session\UserIdFromSignedInSessionToken;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class UserIdFromSignedInSessionTokenTest extends KernelTestBase
{
    public function testUserIdFromSignedInSessionToken(): void
    {
        $userIdFromSignedInSessionToken = $this->getContainer()
            ->get(UserIdFromSignedInSessionToken::class);

        $tokenLastRefreshedAt = \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s.u',
            '2018-02-01 23:55:31.841314'
        );
        $tokenLastRefreshedAtMinusOneMicrosecond = \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s.u',
            '2018-02-01 23:55:31.841313'
        );

        if (is_bool($tokenLastRefreshedAt)) {
            throw new \Exception();
        }

        if (is_bool($tokenLastRefreshedAtMinusOneMicrosecond)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            null,
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_123',
                $tokenLastRefreshedAt
            )
        );
        Assert::assertEquals(
            null,
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_123',
                $tokenLastRefreshedAtMinusOneMicrosecond
            )
        );
        Assert::assertEquals(
            null,
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_1234',
                $tokenLastRefreshedAt
            )
        );
        Assert::assertEquals(
            null,
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_1234',
                $tokenLastRefreshedAtMinusOneMicrosecond
            )
        );

        $this->getProjectionDocumentManager()
            ->persist(
                Session::fromProperties(
                    'session_id_123',
                    'user_id_123',
                    'session_token_123',
                    false,
                    $tokenLastRefreshedAt
                )
            );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(
            null,
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_123',
                $tokenLastRefreshedAt
            )
        );
        Assert::assertEquals(
            'user_id_123',
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_123',
                $tokenLastRefreshedAtMinusOneMicrosecond
            )
        );
        Assert::assertEquals(
            null,
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_1234',
                $tokenLastRefreshedAt
            )
        );
        Assert::assertEquals(
            null,
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_1234',
                $tokenLastRefreshedAtMinusOneMicrosecond
            )
        );

        $this->getProjectionDocumentManager()
            ->persist(
                Session::fromProperties(
                    'session_id_1234',
                    'user_id_1234',
                    'session_token_1234',
                    false,
                    $tokenLastRefreshedAt
                )
            );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(
            null,
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_123',
                $tokenLastRefreshedAt
            )
        );
        Assert::assertEquals(
            'user_id_123',
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_123',
                $tokenLastRefreshedAtMinusOneMicrosecond
            )
        );
        Assert::assertEquals(
            null,
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_1234',
                $tokenLastRefreshedAt
            )
        );
        Assert::assertEquals(
            'user_id_1234',
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_1234',
                $tokenLastRefreshedAtMinusOneMicrosecond
            )
        );
    }

    public function testUserIdFromSignedInSessionTokenWithSignedOutTrue(): void
    {
        $userIdFromSignedInSessionToken = $this->getContainer()
            ->get(UserIdFromSignedInSessionToken::class);

        $tokenLastRefreshedAt = \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s.u',
            '2018-02-01 23:55:31.841314'
        );
        $tokenLastRefreshedAtMinusOneMicrosecond = \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s.u',
            '2018-02-01 23:55:31.841313'
        );

        if (is_bool($tokenLastRefreshedAt)) {
            throw new \Exception();
        }

        if (is_bool($tokenLastRefreshedAtMinusOneMicrosecond)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            null,
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_123',
                $tokenLastRefreshedAt
            )
        );
        Assert::assertEquals(
            null,
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_123',
                $tokenLastRefreshedAtMinusOneMicrosecond
            )
        );

        $this->getProjectionDocumentManager()
            ->persist(
                Session::fromProperties(
                    'session_id_123',
                    'user_id_123',
                    'session_token_123',
                    true,
                    $tokenLastRefreshedAt
                )
            );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(
            null,
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_123',
                $tokenLastRefreshedAt
            )
        );
        Assert::assertEquals(
            null,
            $userIdFromSignedInSessionToken->userIdFromSignedInSessionToken(
                'session_token_123',
                $tokenLastRefreshedAtMinusOneMicrosecond
            )
        );
    }
}
