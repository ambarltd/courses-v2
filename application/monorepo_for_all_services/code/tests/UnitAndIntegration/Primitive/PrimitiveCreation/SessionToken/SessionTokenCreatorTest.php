<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveCreation\SessionToken;

use Galeas\Api\Primitive\PrimitiveCreation\SessionToken\SessionTokenCreator;
use Galeas\Api\Primitive\PrimitiveValidation\Session\SessionTokenValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class SessionTokenCreatorTest extends UnitTestBase
{
    public function testCreate(): void
    {
        for ($i = 0; $i < 20000; ++$i) {
            $sessionToken = SessionTokenCreator::create();

            $sessionTokenHasInvalidLength = 96 !== strlen($sessionToken);
            if ($sessionTokenHasInvalidLength) {
                Assert::fail(sprintf(
                    '%s is %s characters long, but it should be 96 characters long',
                    $sessionToken,
                    strlen($sessionToken)
                ));
            }

            // Assuming the validator class has its tests passing
            $sessionTokenIsNotValid = false === SessionTokenValidator::isValid($sessionToken);
            if ($sessionTokenIsNotValid) {
                Assert::fail(sprintf(
                    '%s is not a valid session token',
                    $sessionToken
                ));
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    public function testRandomnessStatistically(): void
    {
        $firstThreeCharactersArray = [];
        $lastThreeCharactersArray = [];

        for ($i = 0; $i < 100000; ++$i) {
            $sessionToken = SessionTokenCreator::create();

            $firstThreeCharacters = substr($sessionToken, 0, 3);
            $firstThreeCharactersArray[$firstThreeCharacters] = $firstThreeCharacters;

            $lastThreeCharacters = substr($sessionToken, -3, 3);
            $lastThreeCharactersArray[$lastThreeCharacters] = $lastThreeCharacters;
        }

        $firstThreeCharactersCollisions = 100000 - count($firstThreeCharactersArray);
        $lastThreeCharactersCollisions = 100000 - count($lastThreeCharactersArray);

        $errorMessage = 'Testing randomness failed. Unless you are extremely unlucky, this should only fail when you did something wrong.';



        if ($firstThreeCharactersCollisions < 18000 || $firstThreeCharactersCollisions > 20000) {
            Assert::fail($errorMessage);
        }

        if ($lastThreeCharactersCollisions < 18000 || $lastThreeCharactersCollisions > 20000) {
            Assert::fail($errorMessage);
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}
