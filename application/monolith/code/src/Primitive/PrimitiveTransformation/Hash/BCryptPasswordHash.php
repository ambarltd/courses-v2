<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveTransformation\Hash;

abstract class BCryptPasswordHash
{
    public static function hash(
        string $password,
        int $bCryptCost
    ): ?string {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => $bCryptCost]);

        if (is_bool($hash)) {
            return null;
        }

        return $hash;
    }
}
