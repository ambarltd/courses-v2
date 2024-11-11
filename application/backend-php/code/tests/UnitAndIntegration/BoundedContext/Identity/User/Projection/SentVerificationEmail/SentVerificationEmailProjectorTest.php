<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\SentVerificationEmail;

use Galeas\Api\BoundedContext\Identity\User\Projection\SentVerificationEmail\SentVerificationEmail;
use Galeas\Api\BoundedContext\Identity\User\Projection\SentVerificationEmail\SentVerificationEmailProjector;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class SentVerificationEmailProjectorTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testProcessPrimaryEmailVerificationCodeSent(): void
    {
        $processorService = $this->getContainer()
            ->get(SentVerificationEmailProjector::class)
        ;

        $primaryEmailVerificationCodeSent1 = SampleEvents::primaryEmailVerificationCodeSent(
            Id::createNew(),
            5,
            Id::createNew(),
            Id::createNew(),
        );
        $processorService->project($primaryEmailVerificationCodeSent1);

        $primaryEmailVerificationCodeSent2 = SampleEvents::primaryEmailVerificationCodeSent(
            Id::createNew(),
            5,
            Id::createNew(),
            Id::createNew(),
        );
        $processorService->project($primaryEmailVerificationCodeSent2);

        Assert::assertEquals(
            SentVerificationEmail::fromProperties(
                $primaryEmailVerificationCodeSent1->eventId()->id(),
                $primaryEmailVerificationCodeSent1->aggregateId()->id(),
                $primaryEmailVerificationCodeSent1->verificationCodeSent(),
                $primaryEmailVerificationCodeSent1->toEmailAddress(),
                $primaryEmailVerificationCodeSent1->emailContents(),
                $primaryEmailVerificationCodeSent1->fromEmailAddress(),
                $primaryEmailVerificationCodeSent1->subjectLine(),
                $primaryEmailVerificationCodeSent1->recordedOn()
            ),
            $this->findSentVerificationEmail($primaryEmailVerificationCodeSent1->eventId()->id())
        );

        Assert::assertEquals(
            SentVerificationEmail::fromProperties(
                $primaryEmailVerificationCodeSent2->eventId()->id(),
                $primaryEmailVerificationCodeSent2->aggregateId()->id(),
                $primaryEmailVerificationCodeSent2->verificationCodeSent(),
                $primaryEmailVerificationCodeSent2->toEmailAddress(),
                $primaryEmailVerificationCodeSent2->emailContents(),
                $primaryEmailVerificationCodeSent2->fromEmailAddress(),
                $primaryEmailVerificationCodeSent2->subjectLine(),
                $primaryEmailVerificationCodeSent2->recordedOn()
            ),
            $this->findSentVerificationEmail($primaryEmailVerificationCodeSent2->eventId()->id())
        );
    }

    /**
     * @throws \Exception
     */
    private function findSentVerificationEmail(string $eventId): ?SentVerificationEmail
    {
        $queryBuilder = $this->getProjectionDocumentManager()
            ->createQueryBuilder(SentVerificationEmail::class)
        ;

        $queryBuilder->field('id')->equals($eventId);

        $sentVerificationEmail = $queryBuilder
            ->getQuery()
            ->getSingleResult()
        ;

        if ($sentVerificationEmail instanceof SentVerificationEmail) {
            return $sentVerificationEmail;
        }

        if (null === $sentVerificationEmail) {
            return null;
        }

        throw new \Exception('Unexpected type');
    }
}
