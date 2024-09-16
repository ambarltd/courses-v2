<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\HashedPassword;

use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Security\Session\Projection\HashedPassword\HashedPassword;
use Galeas\Api\BoundedContext\Security\Session\Projection\HashedPassword\HashedPasswordProjector;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class HashedPasswordProjectorTest extends KernelTestBase
{
    public function testHashedPasswordProjector(): void
    {
        $HashedPasswordProjector = $this->getContainer()
            ->get(HashedPasswordProjector::class);

        $signedUp1 = SignedUp::fromPropertiesAndDefaultOthers(
            [],
            'primary_email@test.com',
            'password_4123',
            'username_4123',
            true
        );
        $signedUp2 = SignedUp::fromPropertiesAndDefaultOthers(
            [],
            'primary_email_2@test.com',
            'password_2_4123',
            'username_2_4123',
            true
        );

        Assert::assertCount(
            0,
            $this->findHashedPasswordsByUserId(
                $signedUp1->aggregateId()->id()
            )
        );
        Assert::assertCount(
            0,
            $this->findHashedPasswordsByUserId(
                $signedUp2->aggregateId()->id()
            )
        );

        $HashedPasswordProjector->process($signedUp1);
        $HashedPasswordProjector->process($signedUp1); // test idempotency
        Assert::assertCount(
            1,
            $this->findHashedPasswordsByUserId(
                $signedUp1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $signedUp1->aggregateId()->id(),
            $this->findHashedPasswordsByUserId(
                $signedUp1->aggregateId()->id()
            )[0]->getUserId()
        );
        Assert::assertEquals(
            $signedUp1->hashedPassword(),
            $this->findHashedPasswordsByUserId(
                $signedUp1->aggregateId()->id()
            )[0]->getHashedPassword()
        );
        Assert::assertCount(
            0,
            $this->findHashedPasswordsByUserId(
                $signedUp2->aggregateId()->id()
            )
        );

        $HashedPasswordProjector->process($signedUp2);
        Assert::assertCount(
            1,
            $this->findHashedPasswordsByUserId(
                $signedUp1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $signedUp1->aggregateId()->id(),
            $this->findHashedPasswordsByUserId(
                $signedUp1->aggregateId()->id()
            )[0]->getUserId()
        );
        Assert::assertEquals(
            $signedUp1->hashedPassword(),
            $this->findHashedPasswordsByUserId(
                $signedUp1->aggregateId()->id()
            )[0]->getHashedPassword()
        );
        Assert::assertCount(
            1,
            $this->findHashedPasswordsByUserId(
                $signedUp2->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $signedUp2->aggregateId()->id(),
            $this->findHashedPasswordsByUserId(
                $signedUp2->aggregateId()->id()
            )[0]->getUserId()
        );
        Assert::assertEquals(
            $signedUp2->hashedPassword(),
            $this->findHashedPasswordsByUserId(
                $signedUp2->aggregateId()->id()
            )[0]->getHashedPassword()
        );
    }

    /**
     * @return HashedPassword[]
     */
    private function findHashedPasswordsByUserId(string $userId): array
    {
        return array_values(
            $this->getProjectionDocumentManager()
                ->createQueryBuilder(HashedPassword::class)
                ->field('id')->equals($userId)
                ->getQuery()
                ->execute()
                ->toArray()
        );
    }
}
