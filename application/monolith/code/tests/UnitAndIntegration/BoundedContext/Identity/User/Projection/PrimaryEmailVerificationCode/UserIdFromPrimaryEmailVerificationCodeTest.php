<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode;

use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\PrimaryEmailVerificationCode;
use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\UserIdFromPrimaryEmailVerificationCode;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class UserIdFromPrimaryEmailVerificationCodeTest extends KernelTestBase
{
    public function testUserIdFromPrimaryEmailVerificationCode(): void
    {
        $userIdService = $this->getContainer()
            ->get(UserIdFromPrimaryEmailVerificationCode::class);

        Assert::assertNull($userIdService->userIdFromPrimaryEmailVerificationCode('verification_code_1'));
        Assert::assertNull($userIdService->userIdFromPrimaryEmailVerificationCode('verification_code_2'));

        $this->getProjectionDocumentManager()->persist(
            PrimaryEmailVerificationCode::fromUserIdAndVerificationCode(
                'user_id_1',
                'verification_code_1'
            )
        );
        $this->getProjectionDocumentManager()->persist(
            PrimaryEmailVerificationCode::fromUserIdAndVerificationCode(
                'user_id_2',
                'verification_code_2'
            )
        );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertNotNull($userIdService->userIdFromPrimaryEmailVerificationCode('verification_code_1'));
        Assert::assertNotNull($userIdService->userIdFromPrimaryEmailVerificationCode('verification_code_2'));
        Assert::assertNull($userIdService->userIdFromPrimaryEmailVerificationCode('verification_code_3'));

        $userIdToPrimaryEmailVerificationCode = $this->getProjectionDocumentManager()
            ->createQueryBuilder(PrimaryEmailVerificationCode::class)
            ->field('id')->equals('user_id_2')
            ->getQuery()
            ->getSingleResult();

        if (false === ($userIdToPrimaryEmailVerificationCode instanceof PrimaryEmailVerificationCode)) {
            throw new \Exception();
        }
        $userIdToPrimaryEmailVerificationCode->updateVerificationCode(null);
        $this->getProjectionDocumentManager()->flush();

        Assert::assertNotNull($userIdService->userIdFromPrimaryEmailVerificationCode('verification_code_1'));
        Assert::assertNull($userIdService->userIdFromPrimaryEmailVerificationCode('verification_code_2'));
        Assert::assertNull($userIdService->userIdFromPrimaryEmailVerificationCode('verification_code_3'));

        $userIdToPrimaryEmailVerificationCode = $this->getProjectionDocumentManager()
            ->createQueryBuilder(PrimaryEmailVerificationCode::class)
            ->field('id')->equals('user_id_2')
            ->getQuery()
            ->getSingleResult();
        if (false === ($userIdToPrimaryEmailVerificationCode instanceof PrimaryEmailVerificationCode)) {
            throw new \Exception();
        }
        $userIdToPrimaryEmailVerificationCode->updateVerificationCode('verification_code_3');
        $this->getProjectionDocumentManager()->flush();

        Assert::assertNotNull($userIdService->userIdFromPrimaryEmailVerificationCode('verification_code_1'));
        Assert::assertNull($userIdService->userIdFromPrimaryEmailVerificationCode('verification_code_2'));
        Assert::assertNotNull($userIdService->userIdFromPrimaryEmailVerificationCode('verification_code_3'));
    }
}
