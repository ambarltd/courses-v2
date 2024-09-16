<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveCreation\Email;

use Galeas\Api\Primitive\PrimitiveCreation\Email\EmailVerificationCodeCreator;
use Galeas\Api\Primitive\PrimitiveValidation\Email\EmailVerificationCodeValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class EmailVerificationCodeCreatorTest extends UnitTestBase
{
    public function testCreate(): void
    {
        for ($i = 0; $i < 20000; ++$i) {
            $code = EmailVerificationCodeCreator::create();

            $codeHasInvalidLength = 96 !== strlen($code);
            if ($codeHasInvalidLength) {
                Assert::fail(sprintf(
                    '%s is %s characters long, but it should be 96 characters long',
                    $code,
                    strlen($code)
                ));
            }

            // Assuming the validator class has its tests passing
            $codeIsNotValid = false === EmailVerificationCodeValidator::isValid($code);
            if ($codeIsNotValid) {
                Assert::fail(sprintf(
                    '%s is not a valid id',
                    $code
                ));
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    public function testRandomnessStatistically(): void
    {
        $countVerificationCodesThatStartWithThreeZeros = 0;
        $countVerificationCodesThatStartWithThreeNines = 0;
        $countVerificationCodesThatEndWithThreeZeros = 0;
        $countVerificationCodesThatEndWithThreeNines = 0;
        $verificationCodeLength = strlen(EmailVerificationCodeCreator::create());

        for ($i = 0; $i < 20000; ++$i) {
            $verificationCode = EmailVerificationCodeCreator::create();
            $firstThreeCharacters = substr($verificationCode, 0, 3);
            $lastThreeCharacters = substr($verificationCode, $verificationCodeLength - 3, 3);

            if ('000' === $firstThreeCharacters) {
                ++$countVerificationCodesThatStartWithThreeZeros;
            }

            if ('999' === $firstThreeCharacters) {
                ++$countVerificationCodesThatStartWithThreeNines;
            }

            if ('000' === $lastThreeCharacters) {
                ++$countVerificationCodesThatEndWithThreeZeros;
            }

            if ('999' === $lastThreeCharacters) {
                ++$countVerificationCodesThatEndWithThreeNines;
            }
        }

        $errorMessage = 'Testing randomness failed. Unless you are extremely unlucky, this should only fail when you did something wrong.';

        // In 20,000 codes, the odds of not finding one code that starts with three nines is 1 in 500 million.
        Assert::assertGreaterThan(1, $countVerificationCodesThatStartWithThreeZeros, $errorMessage);
        Assert::assertGreaterThan(1, $countVerificationCodesThatStartWithThreeNines, $errorMessage);
        Assert::assertGreaterThan(1, $countVerificationCodesThatEndWithThreeZeros, $errorMessage);
        Assert::assertGreaterThan(1, $countVerificationCodesThatEndWithThreeNines, $errorMessage);

        // Did not calculate the odds of these failing out of pure chance. But they are low as well.
        Assert::assertLessThan(100, $countVerificationCodesThatStartWithThreeZeros, $errorMessage);
        Assert::assertLessThan(100, $countVerificationCodesThatStartWithThreeNines, $errorMessage);
        Assert::assertLessThan(100, $countVerificationCodesThatEndWithThreeZeros, $errorMessage);
        Assert::assertLessThan(100, $countVerificationCodesThatEndWithThreeNines, $errorMessage);
    }
}
