<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode;

use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\PrimaryEmailVerificationCode;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class PrimaryEmailVerificationCodeTest extends UnitTestBase
{
    public function testPrimaryEmailVerificationCode(): void
    {
        // start with code_123_test
        $primaryEmailVerificationCodeObject = PrimaryEmailVerificationCode::fromUserIdAndVerificationCode(
            'user_id_test',
            'code_123_test'
        );
        Assert::assertEquals('user_id_test', $primaryEmailVerificationCodeObject->getUserId());
        Assert::assertEquals('code_123_test', $primaryEmailVerificationCodeObject->getPrimaryEmailVerificationCode());
        // update to null
        $primaryEmailVerificationCodeObject->updateVerificationCode(null);
        Assert::assertEquals('user_id_test', $primaryEmailVerificationCodeObject->getUserId());
        Assert::assertEquals(null, $primaryEmailVerificationCodeObject->getPrimaryEmailVerificationCode());
        //update to code_1234
        $primaryEmailVerificationCodeObject->updateVerificationCode('code_1234_test');
        Assert::assertEquals('user_id_test', $primaryEmailVerificationCodeObject->getUserId());
        Assert::assertEquals('code_1234_test', $primaryEmailVerificationCodeObject->getPrimaryEmailVerificationCode());

        // start with null
        $primaryEmailVerificationCodeObject = PrimaryEmailVerificationCode::fromUserIdAndVerificationCode(
            'user_id_test',
            null
        );
        Assert::assertEquals('user_id_test', $primaryEmailVerificationCodeObject->getUserId());
        Assert::assertEquals(null, $primaryEmailVerificationCodeObject->getPrimaryEmailVerificationCode());
        // update to code_123_test
        $primaryEmailVerificationCodeObject->updateVerificationCode('code_123_test');
        Assert::assertEquals('user_id_test', $primaryEmailVerificationCodeObject->getUserId());
        Assert::assertEquals('code_123_test', $primaryEmailVerificationCodeObject->getPrimaryEmailVerificationCode());
        //update to null
        $primaryEmailVerificationCodeObject->updateVerificationCode(null);
        Assert::assertEquals('user_id_test', $primaryEmailVerificationCodeObject->getUserId());
        Assert::assertEquals(null, $primaryEmailVerificationCodeObject->getPrimaryEmailVerificationCode());
    }
}
