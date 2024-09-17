<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveComparison\Email;

abstract class AreEmailsEquivalent
{
    public static function areEmailsEquivalent(string $email, string $otherEmail): bool
    {
        return strtolower($email) === strtolower($otherEmail);
    }
}
