<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\UserDetails;

use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\GetUserDetails;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\UserDetails;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\ValueObject\VerifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\ValueObject\VerifiedEmailButRequestedNewEmail;
use Galeas\Api\CommonException\ProjectionCannotRead;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ProjectionAndReactionIntegrationTest;

class GetUserDetailsTest extends ProjectionAndReactionIntegrationTest
{
    public function testGetDetails(): void
    {
        /** @var GetUserDetails $getDetailsService */
        $getDetailsService = $this->getContainer()
            ->get(GetUserDetails::class)
        ;

        $this->getProjectionDocumentManager()->persist(UserDetails::fromProperties(
            '1123',
            UnverifiedEmail::fromProperties('unverified@b.com')
        ));
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            $getDetailsService->getUserDetails('1123'),
            [
                'userId' => '1123',
                'primaryEmailStatus' => [
                    'unverifiedEmail' => [
                        'email' => 'unverified@b.com',
                    ],
                ],
            ]
        );

        $this->getProjectionDocumentManager()->persist(UserDetails::fromProperties(
            '1124',
            VerifiedEmail::fromProperties('verified@b.com')
        ));
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            $getDetailsService->getUserDetails('1124'),
            [
                'userId' => '1124',
                'primaryEmailStatus' => [
                    'verifiedEmail' => [
                        'email' => 'verified@b.com',
                    ],
                ],
            ]
        );

        $this->getProjectionDocumentManager()->persist(UserDetails::fromProperties(
            '1125',
            VerifiedEmailButRequestedNewEmail::fromProperties(
                'verified_but_requested_new@c.com',
                'new_requested@c.com',
            )
        ));
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            $getDetailsService->getUserDetails('1125'),
            [
                'userId' => '1125',
                'primaryEmailStatus' => [
                    'verifiedButRequestedNewEmail' => [
                        'verifiedEmail' => 'verified_but_requested_new@c.com',
                        'requestedEmail' => 'new_requested@c.com',
                    ],
                ],
            ]
        );
    }

    public function testGetNoDetails(): void
    {
        $this->expectException(ProjectionCannotRead::class);
        $this->expectExceptionMessageMatches('/Expected UserDetails instance/');

        /** @var GetUserDetails $getDetailsService */
        $getDetailsService = $this->getContainer()
            ->get(GetUserDetails::class)
        ;
        $getDetailsService->getUserDetails('1123');
    }
}
