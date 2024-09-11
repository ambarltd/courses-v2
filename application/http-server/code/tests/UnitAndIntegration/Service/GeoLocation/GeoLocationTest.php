<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Service\GeoLocation;

use Galeas\Api\Service\GeoLocation\GeoCityRepository;
use Galeas\Api\Service\GeoLocation\GeoDatabaseCrash;
use Galeas\Api\Service\GeoLocation\GeoLocation;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Ip\ValidPrivateAndReservedIpsV4AndV6;

class GeoLocationTest extends KernelTestBase
{
    /**
     * @test
     */
    public function testUseGeoLocationFromLatitudeAndLongitude(): void
    {
        $geolocation = $this->getContainer()->get(GeoLocation::class);

        Assert::assertEquals('Greenwich', $geolocation->getCityFromLatitudeAndLongitude(51.5074, 0.1278));
        Assert::assertEquals('United Kingdom', $geolocation->getCountryFromLatitudeAndLongitude(51.5074, 0.1278));

        Assert::assertEquals(51.4869, $geolocation->getCityLatitudeFromLatitudeAndLongitude(51.5074, 0.1278));
        Assert::assertEquals(0.1075, $geolocation->getCityLongitudeFromLatitudeAndLongitude(51.5074, 0.1278));
    }

    /**
     * @test
     */
    public function testUseGeoLocationFromIPV4AndIPV6(): void
    {
        $geolocation = $this->getContainer()->get(GeoLocation::class);
        $ips = [
            '77.96.237.178',
            '8.8.8.8',
            '8.8.8.0',
            '8.8.4.4',
            '4.4.4.4',
        ];

        foreach ($ips as $ip) {
            Assert::assertInternalType('float', $geolocation->getLatitudeFromIp($ip));
            Assert::assertInternalType('float', $geolocation->getLongitudeFromIp($ip));

            Assert::assertInternalType('string', $geolocation->getCityFromIp($ip));
            Assert::assertNotEmpty($geolocation->getCityFromIp($ip));

            Assert::assertInternalType('float', $geolocation->getCityLatitudeFromIp($ip));
            Assert::assertInternalType('float', $geolocation->getCityLongitudeFromIp($ip));

            Assert::assertInternalType('string', $geolocation->getCountryFromIp($ip));
            Assert::assertNotEmpty($geolocation->getCountryFromIp($ip));

            Assert::assertInternalType('string', $geolocation->getAutonomousSystemOrganizationFromIp($ip));
            Assert::assertNotEmpty($geolocation->getAutonomousSystemOrganizationFromIp($ip));

            Assert::assertInternalType('integer', $geolocation->getAutonomousSystemNumberFromIp($ip));
        }
    }

    /**
     * @test
     */
    public function testUseGeoLocationFromPrivateAndReservedIPV4AndIPV6(): void
    {
        $geolocation = $this->getContainer()->get(GeoLocation::class);

        foreach (ValidPrivateAndReservedIpsV4AndV6::listValidIps() as $ip) {
            Assert::assertNull($geolocation->getLatitudeFromIp($ip));
            Assert::assertNull($geolocation->getLongitudeFromIp($ip));

            Assert::assertNull($geolocation->getCityFromIp($ip));
            Assert::assertNull($geolocation->getCityLatitudeFromIp($ip));
            Assert::assertNull($geolocation->getCityLongitudeFromIp($ip));
            Assert::assertNull($geolocation->getCountryFromIp($ip));

            Assert::assertNull($geolocation->getAutonomousSystemOrganizationFromIp($ip));
            Assert::assertNull($geolocation->getAutonomousSystemNumberFromIp($ip));
        }
    }

    /**
     * @test
     */
    public function testGeoLocationDoesNotResolveToBlankCityName(): void
    {
        $geolocation = $this->getContainer()->get(GeoLocation::class);

        // this particular lat,long is nearby a location with a blank city name
        // testing that it finds the next nearest and non blank one
        // the database has 96K+ cities, ~2k without names
        Assert::assertEquals('City of Westminster', $geolocation->getCityFromLatitudeAndLongitude(51.4964, -0.1224));
        Assert::assertEquals('United Kingdom', $geolocation->getCountryFromLatitudeAndLongitude(51.4964, -0.1224));
        Assert::assertEquals(51.5, $geolocation->getCityLatitudeFromLatitudeAndLongitude(51.4964, -0.1224));
        Assert::assertEquals(-0.1167, $geolocation->getCityLongitudeFromLatitudeAndLongitude(51.4964, -0.1224));
    }

    /**
     * @test
     */
    public function useGeoLocationAndGetExceptionsTest(): void
    {
        $geolocation = $this->getContainer()->get(GeoLocation::class);
        $ip = 'x';

        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getLatitudeFromIp($ip);
            }
        );
        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getLongitudeFromIp($ip);
            }
        );
        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getCityFromIp($ip);
            }
        );
        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getCityLatitudeFromIp($ip);
            }
        );
        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getCityLongitudeFromIp($ip);
            }
        );
        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getCountryFromIp($ip);
            }
        );
        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getAutonomousSystemOrganizationFromIp($ip);
            }
        );
        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getAutonomousSystemNumberFromIp($ip);
            }
        );
    }

    /**
     * @test
     */
    public function useBadReaderTest(): void
    {
        $cityIpReader = new BadReader($this->getContainer()->getParameter('file_location.geoip.city_reader'));
        $asnIpReader = new BadReader($this->getContainer()->getParameter('file_location.geoip.asn_reader'));
        $geolocation = new GeoLocation(
            $this->getContainer()->get(GeoCityRepository::class),
            $cityIpReader,
            $asnIpReader
        );
        $ip = '77.96.237.178';

        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getLatitudeFromIp($ip);
            }
        );
        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getLongitudeFromIp($ip);
            }
        );
        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getCityFromIp($ip);
            }
        );
        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getCityLatitudeFromIp($ip);
            }
        );
        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getCityLongitudeFromIp($ip);
            }
        );
        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getCountryFromIp($ip);
            }
        );
        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getAutonomousSystemOrganizationFromIp($ip);
            }
        );
        $this->assertGeoDatabaseCrash(
            function () use ($geolocation, $ip): void {
                $geolocation->getAutonomousSystemNumberFromIp($ip);
            }
        );
    }

    private function assertGeoDatabaseCrash(callable $callable): void
    {
        try {
            $callable();
            Assert::fail('Expected '.GeoDatabaseCrash::class);
        } catch (GeoDatabaseCrash $exception) {
            Assert::assertTrue(true);
        }
    }
}

class BadReader extends \MaxMind\Db\Reader
{
    /**
     * @param string $ip
     *
     * @return float
     *
     * Not type hinted because of warning created for conflict with \MaxMind\Db\Reader::get()
     */
    public function get($ip)
    {
        if ('NoIpWillMatchThis' === $ip) {
            return 10.2;
        }

        throw new GeoDatabaseCrash(new \InvalidArgumentException());
    }
}
