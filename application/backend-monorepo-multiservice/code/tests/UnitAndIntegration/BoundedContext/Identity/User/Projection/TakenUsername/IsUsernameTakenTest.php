<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\TakenUsername;

use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\IsUsernameTaken;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\TakenUsername;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class IsUsernameTakenTest extends KernelTestBase
{
    public function testIsUsernameTaken(): void
    {
        $isUsernameTakenService = $this->getContainer()
            ->get(IsUsernameTaken::class);

        Assert::assertFalse($isUsernameTakenService->isUsernameTaken('test_username_1'));
        Assert::assertFalse($isUsernameTakenService->isUsernameTaken('test_username_2'));

        $this->getProjectionDocumentManager()->persist(
            TakenUsername::fromUserIdAndUsername(
                'user_id_1',
                'teSt_usernAme_1'
            )
        );
        $this->getProjectionDocumentManager()->persist(
            TakenUsername::fromUserIdAndUsername(
                'user_id_2',
                'test_uSernAme_2'
            )
        );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertTrue($isUsernameTakenService->isUsernameTaken('test_username_1'));
        Assert::assertTrue($isUsernameTakenService->isUsernameTaken('test_username_2'));
        Assert::assertFalse($isUsernameTakenService->isUsernameTaken('test_username_3'));

        $takenUsername = $this->getProjectionDocumentManager()
            ->createQueryBuilder(TakenUsername::class)
            ->field('id')->equals('user_id_1')
            ->getQuery()
            ->getSingleResult();

        if (false === ($takenUsername instanceof TakenUsername)) {
            throw new \Exception();
        }
        $takenUsername->changeUsername('test_useRname_3');
        $this->getProjectionDocumentManager()->flush();

        Assert::assertFalse($isUsernameTakenService->isUsernameTaken('test_username_1'));
        Assert::assertTrue($isUsernameTakenService->isUsernameTaken('test_username_2'));
        Assert::assertTrue($isUsernameTakenService->isUsernameTaken('test_username_3'));
    }
}
