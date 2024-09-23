<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\SentVerificationEmail;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\CommonException\ProjectionCannotRead;

class ListSentVerificationEmail
{
    private DocumentManager $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    /**
     * @return array<array{userId: string, verificationCodeSent: string, toEmailAddress: string, emailContents: string, fromEmailAddress: string, subjectLine: string, sentAt: string}>
     *
     * @throws ProjectionCannotRead
     */
    public function list(): array
    {
        try {
            /** @var SentVerificationEmail[] $items */
            $items = $this->projectionDocumentManager
                ->createQueryBuilder(SentVerificationEmail::class)
                ->getQuery()
                ->getIterator()
            ;

            $list = [];
            foreach ($items as $item) {
                $list[] = [
                    'userId' => $item->getId(),
                    'verificationCodeSent' => $item->getVerificationCodeSent(),
                    'toEmailAddress' => $item->getToEmailAddress(),
                    'emailContents' => $item->getEmailContents(),
                    'fromEmailAddress' => $item->getFromEmailAddress(),
                    'subjectLine' => $item->getSubjectLine(),
                    'sentAt' => $item->getSentAt()->format('Y-m-d H:i:s.u e'),
                ];
            }

            return $list;
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
