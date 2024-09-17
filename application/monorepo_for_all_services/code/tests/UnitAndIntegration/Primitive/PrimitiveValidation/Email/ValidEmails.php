<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email;

abstract class ValidEmails
{
    /**
     * @return string[]
     */
    public static function listValidEmails(): array
    {
        return [
            'test@galeas.com',
            'example@example.co.uk',
            'example@example.co.uk.es',
            'someone_someone@example.com',
            'someone.someone@example.com',
            '"user\"name"@example.com',
            '""@example.com',
            'someguy+hislastname@example.co.uk',
            'somegirl-lastname@example.com',
            '"\""@example.com',
            '"\a"@example.com',
            '"test\ test"@example.com',
            '"somegirl,lastname"@example.com',
            '"user,name"@example.io',
            '"user@name"@example.com',
        ];
    }
}
