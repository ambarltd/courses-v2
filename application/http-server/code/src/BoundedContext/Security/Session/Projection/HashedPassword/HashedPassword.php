<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\HashedPassword;

class HashedPassword
{
    /**
     * @var string
     */
    private $hashedPassword;

    /**
     * @var string
     */
    private $id;

    private function __construct()
    {
    }

    public function getHashedPassword(): string
    {
        return $this->hashedPassword;
    }

    public function getUserId(): string
    {
        return $this->id;
    }

    /**
     * @return $this
     */
    public function changeHashedPassword(string $newHashedPassword): self
    {
        $this->hashedPassword = $newHashedPassword;

        return $this;
    }

    public static function fromUserIdAndHashedPassword(
        string $userId,
        string $hashedPassword
    ): self {
        $hashedPasswordObject = new self();
        $hashedPasswordObject->id = $userId;
        $hashedPasswordObject->hashedPassword = $hashedPassword;

        return $hashedPasswordObject;
    }
}
