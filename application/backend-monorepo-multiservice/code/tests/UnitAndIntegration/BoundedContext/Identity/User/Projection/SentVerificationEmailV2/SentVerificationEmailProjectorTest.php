<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\SentVerificationEmailV2;

use Galeas\Api\BoundedContext\Identity\User\Projection\SentVerificationEmailV2\SentVerificationEmail;
use Galeas\Api\BoundedContext\Identity\User\Projection\SentVerificationEmailV2\SentVerificationEmailProjector;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ProjectionAndReactionIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class SentVerificationEmailProjectorTest extends ProjectionAndReactionIntegrationTest
{
    public function testProcessPrimaryEmailVerificationCodeSent(): void
    {
        $processorService = $this->getContainer()
            ->get(SentVerificationEmailProjector::class)
        ;

        $primaryEmailVerificationCodeSent = SampleEvents::primaryEmailVerificationCodeSent(
            Id::createNew(),
            5,
            Id::createNew(),
            Id::createNew(),
        );
        $processorService->project($primaryEmailVerificationCodeSent);

        Assert::assertEquals(
            SentVerificationEmail::fromProperties(
                $primaryEmailVerificationCodeSent->eventId()->id(),
                $primaryEmailVerificationCodeSent->aggregateId()->id(),
                $primaryEmailVerificationCodeSent->verificationCodeSent(),
                $primaryEmailVerificationCodeSent->toEmailAddress(),
                $primaryEmailVerificationCodeSent->emailContents(),
                $primaryEmailVerificationCodeSent->fromEmailAddress(),
                $primaryEmailVerificationCodeSent->subjectLine(),
                $primaryEmailVerificationCodeSent->recordedOn()
            ),
            $this->findSentVerificationEmail($primaryEmailVerificationCodeSent->eventId()->id())
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
