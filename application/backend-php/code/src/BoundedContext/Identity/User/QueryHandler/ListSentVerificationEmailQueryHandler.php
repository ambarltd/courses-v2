<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\QueryHandler;

use Galeas\Api\BoundedContext\Identity\User\Projection\SentVerificationEmail\ListSentVerificationEmail;
use Galeas\Api\BoundedContext\Identity\User\Query\ListSentVerificationEmailQuery;
use Galeas\Api\CommonException\ProjectionCannotRead;

class ListSentVerificationEmailQueryHandler
{
    private ListSentVerificationEmail $listSentVerificationEmail;

    public function __construct(ListSentVerificationEmail $listSentVerificationEmail)
    {
        $this->listSentVerificationEmail = $listSentVerificationEmail;
    }

    /**
     * @return array<array{userId: string, verificationCodeSent: string, toEmailAddress: string, emailContents: string, fromEmailAddress: string, subjectLine: string, sentAt: string}>
     *
     * @throws ProjectionCannotRead
     */
    public function handle(ListSentVerificationEmailQuery $listSentVerificationEmailQuery): array
    {
        return $this->listSentVerificationEmail->list();
    }
}
