<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Service\ODM;

use Doctrine\ODM\MongoDB\Types\Type;
use Galeas\Api\Service\ODM\OverrideDateType;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class OverrideDateTypeTest extends UnitTestBase
{
    private OverrideDateType $overrideDateType;

    public function setUp(): void
    {
        parent::setUp();
        Type::registerType('date_override_for_testing', OverrideDateType::class);
        $overrideDateType = Type::getType('date_override_for_testing');

        if (!($overrideDateType instanceof OverrideDateType)) {
            throw new \Exception();
        }

        $this->overrideDateType = $overrideDateType;
    }

    public function testGetDateTimeImmutable(): void
    {
        $value = '2018-06-21T14:36:34.887181+00:00';
        $expectedDateTimeImmutable = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.uP', $value);

        Assert::assertInstanceOf(
            \DateTimeImmutable::class,
            OverrideDateType::getDateTimeImmutable($value)
        );
        Assert::assertEquals(
            $expectedDateTimeImmutable,
            OverrideDateType::getDateTimeImmutable($value)
        );
        Assert::assertEquals(
            null,
            OverrideDateType::getDateTimeImmutable(null)
        );
    }

    public function testConvertToDatabaseValue(): void
    {
        $dateTimeImmutable = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.uP', '2018-06-21T14:36:34.887181+00:00');

        if (is_bool($dateTimeImmutable)) {
            throw new \Exception();
        }
        Assert::assertEquals(
            '2018-06-21T14:36:34.887181+00:00',
            $this->overrideDateType->convertToDatabaseValue($dateTimeImmutable)
        );

        // test values are saved as UTC for sortability
        $dateTimeImmutable = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.uP', '2018-06-21T14:36:34.887181+04:00');
        if (is_bool($dateTimeImmutable)) {
            throw new \Exception();
        }
        Assert::assertEquals(
            '2018-06-21T10:36:34.887181+00:00',
            $this->overrideDateType->convertToDatabaseValue($dateTimeImmutable)
        );
    }

    public function testConvertToPHPValue(): void
    {
        $value = '2018-06-21T14:36:34.887181+00:00';
        $expectedDateTimeImmutable = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.uP', $value);

        Assert::assertInstanceOf(
            \DateTimeImmutable::class,
            $this->overrideDateType->convertToPHPValue($value)
        );
        Assert::assertEquals(
            $expectedDateTimeImmutable,
            $this->overrideDateType->convertToPHPValue($value)
        );
        Assert::assertEquals(
            null,
            $this->overrideDateType->convertToPHPValue(null)
        );
    }

    public function testClosureToMongo(): void
    {
        // a bit of a redundant test, but let's make sure that things can't be modified without this test failing.
        Assert::assertEquals(
            'if ($value === null) { $return = null; } else { $return = \\'.OverrideDateType::class.'::getUTCDateString($value); }',
            $this->overrideDateType->closureToMongo()
        );
    }

    public function testClosureToPHP(): void
    {
        // a bit of a redundant test, but let's make sure that things can't be modified without this test failing.
        Assert::assertEquals(
            'if ($value === null) { $return = null; } else { $return = \\'.OverrideDateType::class.'::getDateTimeImmutable($value); }',
            $this->overrideDateType->closureToPHP()
        );
    }
}
