<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email;

abstract class InvalidEmails
{
    /**
     * @return string[]
     */
    public static function listInvalidEmails(): array
    {
        return [
            '',
            ' ',
            'bogus',
            'bogus.com',
            'someone@@galeas.com',
            'someone@galeas.com oops',
            'someone with spaces@galeas.com',
            '(ohno_@example.co.uk)',
            'example@localhost',
            'example@127.0.0.1',
            'username,@example.com',
            '\rwhat@example.com',
            '\nwhat@example.com',
            'test@example.com<',
            'test@example.com>',
            'tes/t@example.com>',
            'test@exa@mple.com>',
            str_repeat('a', 320).'@toolong.example.com',
        ];
    }
}
