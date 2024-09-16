<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\ValueObject;

use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class EmailTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $email = Email::fromEmail('test@example.com');

        Assert::assertEquals('test@example.com', $email->email());
    }
}
