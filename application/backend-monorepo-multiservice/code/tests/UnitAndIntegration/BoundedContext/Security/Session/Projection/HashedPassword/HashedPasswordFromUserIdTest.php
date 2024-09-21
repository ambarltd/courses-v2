<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\HashedPassword;

use Galeas\Api\BoundedContext\Security\Session\Projection\HashedPassword\HashedPassword;
use Galeas\Api\BoundedContext\Security\Session\Projection\HashedPassword\HashedPasswordFromUserId;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class HashedPasswordFromUserIdTest extends KernelTestBase
{
    public function testHashedPasswordFromUserId(): void
    {
        $hashedPasswordFromUserIdService = $this->getContainer()
            ->get(HashedPasswordFromUserId::class);

        Assert::assertEquals(
            null,
            $hashedPasswordFromUserIdService->hashedPasswordFromUserId('user_id_1')
        );
        Assert::assertEquals(
            null,
            $hashedPasswordFromUserIdService->hashedPasswordFromUserId('user_id_2')
        );

        $this->getProjectionDocumentManager()
            ->persist(
                HashedPassword::fromUserIdAndHashedPassword(
                    'user_id_1',
                    'hashed_password_1_a'
                )
            );
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            'hashed_password_1_a',
            $hashedPasswordFromUserIdService->hashedPasswordFromUserId('user_id_1')
        );
        Assert::assertEquals(
            null,
            $hashedPasswordFromUserIdService->hashedPasswordFromUserId('user_id_2')
        );

        $this->getProjectionDocumentManager()
            ->persist(
                HashedPassword::fromUserIdAndHashedPassword(
                    'user_id_2',
                    'hashed_password_2_a'
                )
            );
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            'hashed_password_1_a',
            $hashedPasswordFromUserIdService->hashedPasswordFromUserId('user_id_1')
        );
        Assert::assertEquals(
            'hashed_password_2_a',
            $hashedPasswordFromUserIdService->hashedPasswordFromUserId('user_id_2')
        );

        /** @var HashedPassword $hashedPasswordUserId1 */
        $hashedPasswordUserId1 = $this->getProjectionDocumentManager()
            ->createQueryBuilder(HashedPassword::class)
            ->field('id')->equals('user_id_1')
            ->getQuery()
            ->getSingleResult();
        $hashedPasswordUserId1->changeHashedPassword('hashed_password_1_b');
        $this->getProjectionDocumentManager()->persist($hashedPasswordUserId1);
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            'hashed_password_1_b',
            $hashedPasswordFromUserIdService->hashedPasswordFromUserId('user_id_1')
        );
        Assert::assertEquals(
            'hashed_password_2_a',
            $hashedPasswordFromUserIdService->hashedPasswordFromUserId('user_id_2')
        );

        /** @var HashedPassword $hashedPasswordUserId2 */
        $hashedPasswordUserId2 = $this->getProjectionDocumentManager()
            ->createQueryBuilder(HashedPassword::class)
            ->field('id')->equals('user_id_2')
            ->getQuery()
            ->getSingleResult();
        $hashedPasswordUserId2->changeHashedPassword('hashed_password_2_b');
        $this->getProjectionDocumentManager()->persist($hashedPasswordUserId2);
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            'hashed_password_1_b',
            $hashedPasswordFromUserIdService->hashedPasswordFromUserId('user_id_1')
        );
        Assert::assertEquals(
            'hashed_password_2_b',
            $hashedPasswordFromUserIdService->hashedPasswordFromUserId('user_id_2')
        );
    }
}
