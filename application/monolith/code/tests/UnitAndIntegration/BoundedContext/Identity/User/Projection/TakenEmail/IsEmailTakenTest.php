<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\TakenEmail;

use Galeas\Api\BoundedContext\Identity\User\Projection\TakenEmail\IsEmailTaken;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenEmail\TakenEmail;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class IsEmailTakenTest extends KernelTestBase
{
    public function testIsEmailTaken(): void
    {
        $isEmailTakenService = $this->getContainer()
            ->get(IsEmailTaken::class);

        Assert::assertFalse($isEmailTakenService->isEmailTaken('test_1@example.com'));
        Assert::assertFalse($isEmailTakenService->isEmailTaken('test_2@example.com'));

        $this->getProjectionDocumentManager()->persist(
            TakenEmail::fromUserIdAndEmails(
                'user_id_1',
                'test_1@example.com',
                null
            )
        );
        $this->getProjectionDocumentManager()->persist(
            TakenEmail::fromUserIdAndEmails(
                'user_id_2',
                null,
                'test_2@example.com'
            )
        );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertTrue($isEmailTakenService->isEmailTaken('test_1@example.com'));
        Assert::assertTrue($isEmailTakenService->isEmailTaken('tEst_1@example.com'));
        Assert::assertFalse($isEmailTakenService->isEmailTaken('test_1@example.co'));

        Assert::assertTrue($isEmailTakenService->isEmailTaken('test_2@example.com'));
        Assert::assertTrue($isEmailTakenService->isEmailTaken('tEst_2@example.com'));
        Assert::assertFalse($isEmailTakenService->isEmailTaken('test_2@example.co'));

        $takenEmail = $this->getProjectionDocumentManager()
            ->createQueryBuilder(TakenEmail::class)
            ->field('id')->equals('user_id_1')
            ->getQuery()
            ->getSingleResult();

        if (false === ($takenEmail instanceof TakenEmail)) {
            throw new \Exception();
        }

        $takenEmail->changeEmails('test_1@example.co', null);
        $this->getProjectionDocumentManager()->flush();

        Assert::assertFalse($isEmailTakenService->isEmailTaken('test_1@example.com'));
        Assert::assertFalse($isEmailTakenService->isEmailTaken('tEst_1@example.com'));
        Assert::assertTrue($isEmailTakenService->isEmailTaken('test_1@example.co'));
        Assert::assertTrue($isEmailTakenService->isEmailTaken('tEst_1@example.co'));
    }
}
