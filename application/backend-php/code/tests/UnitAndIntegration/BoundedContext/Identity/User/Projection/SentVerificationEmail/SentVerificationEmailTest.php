<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\SentVerificationEmail;

use Galeas\Api\BoundedContext\Identity\User\Projection\SentVerificationEmail\SentVerificationEmail;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;

class SentVerificationEmailTest extends UnitTest
{
    public function testPrimaryEmailVerificationCode(): void
    {
        $sentVerificationEmail = SentVerificationEmail::fromProperties(
            'event_id_1',
            'user_id_1',
            'verification_code_1',
            'to_email_address',
            'email_contents',
            'from_email_address',
            'subject_line',
            new \DateTimeImmutable('2021-01-01 00:00:00')
        );

        Assert::assertEquals('event_id_1', $sentVerificationEmail->getId());
        Assert::assertEquals('user_id_1', $sentVerificationEmail->getUserId());
        Assert::assertEquals('verification_code_1', $sentVerificationEmail->getVerificationCodeSent());
        Assert::assertEquals('to_email_address', $sentVerificationEmail->getToEmailAddress());
        Assert::assertEquals('email_contents', $sentVerificationEmail->getEmailContents());
        Assert::assertEquals('from_email_address', $sentVerificationEmail->getFromEmailAddress());
        Assert::assertEquals('subject_line', $sentVerificationEmail->getSubjectLine());
        Assert::assertEquals(new \DateTimeImmutable('2021-01-01 00:00:00'), $sentVerificationEmail->getSentAt());
    }
}
