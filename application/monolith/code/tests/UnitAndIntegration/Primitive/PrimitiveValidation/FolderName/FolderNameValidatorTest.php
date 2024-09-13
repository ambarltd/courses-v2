<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\FolderName;

use Galeas\Api\Primitive\PrimitiveValidation\FolderName\FolderNameValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class FolderNameValidatorTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testValid(): void
    {
        foreach (ValidFolderNames::listValidFolderNames() as $folderName) {
            if (false === FolderNameValidator::isValid($folderName)) {
                Assert::fail($folderName.' should be valid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    /**
     * @test
     */
    public function testInvalid(): void
    {
        foreach (InvalidFolderNames::listInvalidFolderNames() as $folderName) {
            if (true === FolderNameValidator::isValid($folderName)) {
                Assert::fail($folderName.' should be invalid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}
