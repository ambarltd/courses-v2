<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\ValueObject;

class VerifiedButRequestedNewEmail
{
    private Email $verifiedEmail;

    private Email $requestedEmail;

    private VerificationCode $verificationCode;

    private function __construct(
        Email $verifiedEmail,
        Email $requestedEmail,
        VerificationCode $verificationCode
    ) {
        $this->verifiedEmail = $verifiedEmail;
        $this->requestedEmail = $requestedEmail;
        $this->verificationCode = $verificationCode;
    }

    public function verifiedEmail(): Email
    {
        return $this->verifiedEmail;
    }

    public function requestedEmail(): Email
    {
        return $this->requestedEmail;
    }

    public function verificationCode(): VerificationCode
    {
        return $this->verificationCode;
    }

    public static function fromEmailsAndVerificationCode(
        Email $verifiedEmail,
        Email $requestedEmail,
        VerificationCode $verificationCode
    ): VerifiedButRequestedNewEmail {
        return new self(
            $verifiedEmail,
            $requestedEmail,
            $verificationCode
        );
    }
}
