<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\ValueObject;

class HashedPassword
{
    private string $hash;

    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public function hash(): string
    {
        return $this->hash;
    }

    public static function fromHash(string $hash): HashedPassword
    {
        return new self($hash);
    }
}
