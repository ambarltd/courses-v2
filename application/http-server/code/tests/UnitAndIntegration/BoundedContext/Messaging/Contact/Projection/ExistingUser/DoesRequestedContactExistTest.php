<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ExistingUser;

use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ExistingUser\DoesRequestedContactExist;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ExistingUser\ExistingUser;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class DoesRequestedContactExistTest extends KernelTestBase
{
    /**
     * @test
     */
    public function testDoesRequestedContactExist(): void
    {
        $doesRequestedContactExist = $this->getContainer()
            ->get(DoesRequestedContactExist::class);

        Assert::assertEquals(
            false,
            $doesRequestedContactExist->doesRequestedContactExist('user_id_1')
        );
        Assert::assertEquals(
            false,
            $doesRequestedContactExist->doesRequestedContactExist('user_id_2')
        );

        $this->getProjectionDocumentManager()
            ->persist(
                ExistingUser::fromUserId(
                    'user_id_1'
                )
            );
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            true,
            $doesRequestedContactExist->doesRequestedContactExist('user_id_1')
        );
        Assert::assertEquals(
            false,
            $doesRequestedContactExist->doesRequestedContactExist('user_id_2')
        );

        $this->getProjectionDocumentManager()
            ->persist(
                ExistingUser::fromUserId(
                    'user_id_2'
                )
            );
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            true,
            $doesRequestedContactExist->doesRequestedContactExist('user_id_1')
        );
        Assert::assertEquals(
            true,
            $doesRequestedContactExist->doesRequestedContactExist('user_id_2')
        );
    }
}
