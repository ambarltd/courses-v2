<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ExistingUser;

use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ExistingUser\ExistingUser;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ExistingUser\ExistingUserProcessor;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class ExistingUserProcessorTest extends KernelTestBase
{
    /**
     * @test
     */
    public function testExistingUserProcessor(): void
    {
        $existingUserProcessor = $this->getContainer()
            ->get(ExistingUserProcessor::class);

        $signedUp1 = SignedUp::fromProperties(
            [],
            'primary_email@test.com',
            'password_4123',
            'username_4123',
            true
        );
        $signedUp2 = SignedUp::fromProperties(
            [],
            'primary_email_2@test.com',
            'password_2_4123',
            'username_2_4123',
            true
        );

        Assert::assertCount(
            0,
            $this->findExistingUserByUserId(
                $signedUp1->aggregateId()->id()
            )
        );
        Assert::assertCount(
            0,
            $this->findExistingUserByUserId(
                $signedUp2->aggregateId()->id()
            )
        );

        $existingUserProcessor->process($signedUp1);
        $existingUserProcessor->process($signedUp1); // test idempotency
        Assert::assertCount(
            1,
            $this->findExistingUserByUserId(
                $signedUp1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $signedUp1->aggregateId()->id(),
            $this->findExistingUserByUserId(
                $signedUp1->aggregateId()->id()
            )[0]->getUserId()
        );
        Assert::assertCount(
            0,
            $this->findExistingUserByUserId(
                $signedUp2->aggregateId()->id()
            )
        );

        $existingUserProcessor->process($signedUp2);
        Assert::assertCount(
            1,
            $this->findExistingUserByUserId(
                $signedUp2->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $signedUp2->aggregateId()->id(),
            $this->findExistingUserByUserId(
                $signedUp2->aggregateId()->id()
            )[0]->getUserId()
        );
        Assert::assertCount(
            1,
            $this->findExistingUserByUserId(
                $signedUp2->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $signedUp2->aggregateId()->id(),
            $this->findExistingUserByUserId(
                $signedUp2->aggregateId()->id()
            )[0]->getUserId()
        );
    }

    /**
     * @return ExistingUser[]
     */
    private function findExistingUserByUserId(string $userId): array
    {
        return array_values(
            $this->getProjectionDocumentManager()
                ->createQueryBuilder(ExistingUser::class)
                ->field('id')->equals($userId)
                ->getQuery()
                ->execute()
                ->toArray()
        );
    }
}
