<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\QueryHandler;

use Galeas\Api\BoundedContext\Identity\User\Projection\SentVerificationEmail\ListSentVerificationEmail;
use Galeas\Api\BoundedContext\Identity\User\Query\ListSentVerificationEmailQuery;
use Galeas\Api\BoundedContext\Identity\User\QueryHandler\ListSentVerificationEmailQueryHandler;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerUnitTest;

class ListSentVerificationEmailQueryHandlerTest extends HandlerUnitTest
{
    public function testHandle(): void
    {
        $query = new ListSentVerificationEmailQuery();

        $listSentVerificationEmail = $this->mockForCommandHandlerWithReturnValue(
            ListSentVerificationEmail::class,
            'list',
            ['foo' => 'bar']
        );
        Assert::assertEquals(
            [
                'foo' => 'bar',
            ],
            (new ListSentVerificationEmailQueryHandler($listSentVerificationEmail))->handle($query)
        );
    }
}
