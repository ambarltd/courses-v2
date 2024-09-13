<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Common\Event;

use Galeas\Api\Common\Event\PayloadMapper;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class PayloadMapperTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testToAndFromJsonWithArrays(): void
    {
        $arrayPayload = [
            1,
            'a' => 'a_1',
            'b' => [1, 2, 3],
            'c' => [
                'c_1' => 32,
                'c_2' => 33,
                Id::createNew(),
            ],
            12 => new \DateTimeImmutable(),
            '123' => true,
            'abcdefg' => null,
            '1312' => 13.020123,
            'abcdef' => 14,
            [
                'test' => new \DateTimeImmutable(),
                'test 2' => [
                    'test 3' => [
                        [
                            new \DateTimeImmutable(),
                            Id::createNew(),
                        ],
                    ],
                ],
                'another test' => [5, 6, 7],
            ],
            '123123' => Id::createNew(),
        ];

        $jsonPayload = PayloadMapper::arrayPayloadToJsonPayload(
            $arrayPayload,
            true
        );

        $unserializedJsonPayload = PayloadMapper::jsonPayloadToArrayPayload($jsonPayload);

        Assert::assertEquals(
            $arrayPayload,
            $unserializedJsonPayload
        );
    }

    /**
     * @test
     */
    public function testToAndFromJsonWithoutArrays(): void
    {
        $arrayPayload = [
            1,
            'a' => 'a_1',
            12 => new \DateTimeImmutable(),
            '123' => true,
            'abcdef' => null,
            '1312' => 13.020123,
            'abcdefg' => 14,
            '123123' => Id::createNew(),
        ];

        $jsonPayload = PayloadMapper::arrayPayloadToJsonPayload(
            $arrayPayload,
            false
        );

        $unserializedJsonPayload = PayloadMapper::jsonPayloadToArrayPayload($jsonPayload);

        Assert::assertEquals(
            $arrayPayload,
            $unserializedJsonPayload
        );
    }

    /**
     * @expectedException \Galeas\Api\Common\Event\Exception\ArraysNotAllowedWhenMappingPayload
     */
    public function testArraysNotAllowedWhenMappingPayload(): void
    {
        $arrayPayload = [
            1,
            'a' => 'a_1',
            'b' => [1, 2, 3],
        ];

        PayloadMapper::arrayPayloadToJsonPayload(
            $arrayPayload,
            false
        );
    }

    /**
     * @expectedException \Galeas\Api\Common\Event\Exception\PropertyIsOfInvalidType
     */
    public function testPropertyIsOfInvalidType(): void
    {
        $arrayPayload = [
            1,
            'a' => 'a_1',
            'b' => [1, 2, 3],
            new \DateTime(),
        ];

        PayloadMapper::arrayPayloadToJsonPayload(
            $arrayPayload,
            true
        );
    }
}
