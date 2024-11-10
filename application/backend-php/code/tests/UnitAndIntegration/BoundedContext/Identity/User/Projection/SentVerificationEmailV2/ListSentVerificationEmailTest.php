<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\SentVerificationEmailV2;

use Galeas\Api\BoundedContext\Identity\User\Projection\SentVerificationEmailV2\ListSentVerificationEmail;
use Galeas\Api\BoundedContext\Identity\User\Projection\SentVerificationEmailV2\SentVerificationEmail;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;

class ListSentVerificationEmailTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testList(): void
    {
        /** @var ListSentVerificationEmail $listSentVerificationEmail */
        $listSentVerificationEmail = $this->getContainer()
            ->get(ListSentVerificationEmail::class)
        ;

        Assert::assertEmpty($listSentVerificationEmail->list());

        $sent1 = SentVerificationEmail::fromProperties(
            'event_id_1',
            'user_id_1',
            'verification_code_1',
            'to_email_address_1',
            'email_contents_1',
            'from_email_address_1',
            'subject_line_1',
            new \DateTimeImmutable('2021-01-01 00:00:00')
        );
        $this->getProjectionDocumentManager()->persist($sent1);
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            [
                [
                    'eventId' => 'event_id_1',
                    'userId' => 'user_id_1',
                    'verificationCodeSent' => 'verification_code_1',
                    'toEmailAddress' => 'to_email_address_1',
                    'emailContents' => 'email_contents_1',
                    'fromEmailAddress' => 'from_email_address_1',
                    'subjectLine' => 'subject_line_1',
                    'sentAt' => '2021-01-01 00:00:00.000000 UTC',
                ],
            ],
            $listSentVerificationEmail->list()
        );

        $sent2 = SentVerificationEmail::fromProperties(
            'event_id_2',
            'user_id_2',
            'verification_code_2',
            'to_email_address_2',
            'email_contents_2',
            'from_email_address_2',
            'subject_line_2',
            new \DateTimeImmutable('2021-01-02 00:00:00')
        );
        $this->getProjectionDocumentManager()->persist($sent2);
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            [
                [
                    'eventId' => 'event_id_1',
                    'userId' => 'user_id_1',
                    'verificationCodeSent' => 'verification_code_1',
                    'toEmailAddress' => 'to_email_address_1',
                    'emailContents' => 'email_contents_1',
                    'fromEmailAddress' => 'from_email_address_1',
                    'subjectLine' => 'subject_line_1',
                    'sentAt' => '2021-01-01 00:00:00.000000 UTC',
                ],
                [
                    'eventId' => 'event_id_2',
                    'userId' => 'user_id_2',
                    'verificationCodeSent' => 'verification_code_2',
                    'toEmailAddress' => 'to_email_address_2',
                    'emailContents' => 'email_contents_2',
                    'fromEmailAddress' => 'from_email_address_2',
                    'subjectLine' => 'subject_line_2',
                    'sentAt' => '2021-01-02 00:00:00.000000 UTC',
                ],
            ],
            $listSentVerificationEmail->list()
        );
    }
}
