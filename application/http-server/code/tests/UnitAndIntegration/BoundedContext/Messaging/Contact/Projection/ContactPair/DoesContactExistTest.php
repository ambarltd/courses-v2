<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ContactPair;

use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactPair\ContactPair;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactPair\DoesContactExist;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class DoesContactExistTest extends KernelTestBase
{
    /**
     * @test
     *
     * @throws \Exception
     */
    public function testDoesContactExist(): void
    {
        $doesContactExist = $this->getContainer()
            ->get(DoesContactExist::class);

        Assert::assertEquals(
            false,
            $doesContactExist->doesContactExist('contact_1', 'contact_2')
        );
        Assert::assertEquals(
            false,
            $doesContactExist->doesContactExist('contact_2', 'contact_3')
        );
        Assert::assertEquals(
            false,
            $doesContactExist->doesContactExist('contact_a', 'contact_b')
        );

        $this->getProjectionDocumentManager()
            ->persist(
                ContactPair::fromProperties(
                    'contact_1_and_contact_2',
                    'contact_1',
                    'contact_2'
                )
            );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(
            true,
            $doesContactExist->doesContactExist('contact_1', 'contact_2')
        );
        Assert::assertEquals(
            false,
            $doesContactExist->doesContactExist('contact_2', 'contact_3')
        );
        Assert::assertEquals(
            false,
            $doesContactExist->doesContactExist('contact_a', 'contact_b')
        );

        $this->getProjectionDocumentManager()
            ->persist(
                ContactPair::fromProperties(
                    'contact_2_and_contact_3',
                    'contact_2',
                    'contact_3'
                )
            );
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            true,
            $doesContactExist->doesContactExist('contact_1', 'contact_2')
        );
        Assert::assertEquals(
            true,
            $doesContactExist->doesContactExist('contact_2', 'contact_3')
        );
        Assert::assertEquals(
            false,
            $doesContactExist->doesContactExist('contact_a', 'contact_b')
        );

        $this->getProjectionDocumentManager()
            ->persist(
                ContactPair::fromProperties(
                    'contact_a_and_contact_b',
                    'contact_a',
                    'contact_b'
                )
            );
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            true,
            $doesContactExist->doesContactExist('contact_1', 'contact_2')
        );
        Assert::assertEquals(
            true,
            $doesContactExist->doesContactExist('contact_2', 'contact_3')
        );
        Assert::assertEquals(
            true,
            $doesContactExist->doesContactExist('contact_a', 'contact_b')
        );

        $removeThisPair = $this->getProjectionDocumentManager()
            ->createQueryBuilder(ContactPair::class)
            ->field('id')->equals('contact_2_and_contact_3')
            ->getQuery()
            ->getSingleResult();

        if (false === is_object($removeThisPair)) {
            throw new \Exception();
        }

        $this->getProjectionDocumentManager()->remove($removeThisPair);
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(
            true,
            $doesContactExist->doesContactExist('contact_1', 'contact_2')
        );
        Assert::assertEquals(
            false,
            $doesContactExist->doesContactExist('contact_2', 'contact_3')
        );
        Assert::assertEquals(
            true,
            $doesContactExist->doesContactExist('contact_a', 'contact_b')
        );
    }
}
