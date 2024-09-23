<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\SentVerificationEmail;

class SentVerificationEmail
{
    private string $id;

    private string $verificationCodeSent;

    private string $toEmailAddress;

    private string $emailContents;

    private string $fromEmailAddress;

    private string $subjectLine;

    private \DateTimeImmutable $sentAt;

    private function __construct() {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getVerificationCodeSent(): string
    {
        return $this->verificationCodeSent;
    }

    public function getToEmailAddress(): string
    {
        return $this->toEmailAddress;
    }

    public function getEmailContents(): string
    {
        return $this->emailContents;
    }

    public function getFromEmailAddress(): string
    {
        return $this->fromEmailAddress;
    }

    public function getSubjectLine(): string
    {
        return $this->subjectLine;
    }

    public function getSentAt(): \DateTimeImmutable
    {
        return $this->sentAt;
    }

    public static function fromProperties(
        string $userId,
        string $verificationCodeSent,
        string $toEmailAddress,
        string $emailContents,
        string $fromEmailAddress,
        string $subjectLine,
        \DateTimeImmutable $sentAt
    ): self {
        $sentVerificationEmail = new self();
        $sentVerificationEmail->id = $userId;
        $sentVerificationEmail->verificationCodeSent = $verificationCodeSent;
        $sentVerificationEmail->toEmailAddress = $toEmailAddress;
        $sentVerificationEmail->emailContents = $emailContents;
        $sentVerificationEmail->fromEmailAddress = $fromEmailAddress;
        $sentVerificationEmail->subjectLine = $subjectLine;
        $sentVerificationEmail->sentAt = $sentAt;

        return $sentVerificationEmail;
    }
}
