<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\SentVerificationEmail;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerificationCodeSent;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\CommonException\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class SentVerificationEmailProjector implements EventProjector
{
    private DocumentManager $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function project(Event $event): void
    {
        try {
            if (false === ($event instanceof PrimaryEmailVerificationCodeSent)) {
                return;
            }

            /** @var null|SentVerificationEmail $sentVerificationEmail */
            $sentVerificationEmail = $this->projectionDocumentManager
                ->createQueryBuilder(SentVerificationEmail::class)
                ->field('id')->equals($event->aggregateId()->id())
                ->getQuery()
                ->getSingleResult()
            ;

            if (null !== $sentVerificationEmail) {
                return;
            }

            $sentVerificationEmail = SentVerificationEmail::fromProperties(
                $event->aggregateId()->id(),
                $event->verificationCodeSent(),
                $event->toEmailAddress(),
                $event->emailContents(),
                $event->fromEmailAddress(),
                $event->subjectLine(),
                $event->recordedOn()
            );

            $this->projectionDocumentManager->persist($sentVerificationEmail);
            $this->projectionDocumentManager->flush();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
