<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveCreation\Id;

use Galeas\Api\Primitive\PrimitiveCreation\Id\IdCreator;
use Galeas\Api\Primitive\PrimitiveValidation\Id\IdValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class IdCreatorTest extends UnitTestBase
{
    public function testCreate(): void
    {
        for ($i = 0; $i < 20000; ++$i) {
            $id = IdCreator::create();

            $idHasInvalidLength = 56 !== strlen($id);
            if ($idHasInvalidLength) {
                Assert::fail(sprintf(
                    '%s is %s characters long, but it should be 56 characters long',
                    $id,
                    strlen($id)
                ));
            }

            // Assuming the validator class has its tests passing
            $idIsNotValid = false === IdValidator::isValid($id);
            if ($idIsNotValid) {
                Assert::fail(sprintf(
                    '%s is not a valid id',
                    $id
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
            $id = IdCreator::create();

            $firstThreeCharacters = substr($id, 0, 3);
            $firstThreeCharactersArray[$firstThreeCharacters] = $firstThreeCharacters;

            $lastThreeCharacters = substr($id, -3, 3);
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
