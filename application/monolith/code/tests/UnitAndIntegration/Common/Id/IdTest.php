<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Common\Id;

use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Primitive\PrimitiveValidation\Id\IdValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Id\InvalidIds;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Id\ValidIds;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class IdTest extends UnitTestBase
{
    public function testWithValidIds(): void
    {
        foreach (ValidIds::listValidIds() as $idString) {
            $id = Id::fromId($idString);

            if ($idString !== $id->id()) {
                Assert::fail(sprintf(
                    'Expected %s, got %s',
                    $idString,
                    $id->id()
                ));
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    public function testInvalidIds(): void
    {
        foreach (InvalidIds::listInvalidIds() as $id) {
            try {
                Id::fromId($id);
            } catch (InvalidId $exception) {
                continue;
            }

            Assert::fail($id.' should be invalid.');
        }

        Assert::assertTrue(true); //prevents flagging as risky test
    }

    public function testCreate(): void
    {
        for ($i = 0; $i < 100; ++$i) {
            $id = Id::createNew();
            if (false === IdValidator::isValid($id->id())) {
                // validator test must be passing for this to be relevant
                Assert::fail($id->id().' should be valid');
            }
        }

        Assert::assertTrue(true); //prevents flagging as risky test
    }

    public function testCreateNewByHashing(): void
    {
        for ($i = 0; $i < 100; ++$i) {
            $id = Id::createNewByHashing(strval($i));
            if (false === IdValidator::isValid($id->id())) {
                // validator test must be passing for this to be relevant
                Assert::fail($id->id().' should be valid');
            }
        }

        Assert::assertTrue(true); //prevents flagging as risky test
    }
}
