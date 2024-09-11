<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\ValueObject;

class AccountDetails
{
    /**
     * @var string
     */
    private $username;

    /**
     * This denotes if the user has accepted the terms of use.
     * It's not always true; sometimes a user may have not created his/her own account.
     *
     * @var bool
     */
    private $termsOfUseAccepted;

    private function __construct(
        string $username,
        bool $termsOfUseAccepted
    ) {
        $this->username = $username;
        $this->termsOfUseAccepted = $termsOfUseAccepted;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function termsOfUseAccepted(): bool
    {
        return $this->termsOfUseAccepted;
    }

    public static function fromDetails(
        string $username,
        bool $termsOfUseAccepted
    ): AccountDetails {
        return new self($username, $termsOfUseAccepted);
    }
}
