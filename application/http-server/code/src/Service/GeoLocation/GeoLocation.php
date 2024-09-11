<?php

declare(strict_types=1);

namespace Galeas\Api\Service\GeoLocation;

use Galeas\Api\Primitive\PrimitiveValidation\Ip\IpV4AndV6Validator;
use Galeas\Api\Primitive\PrimitiveValidation\Ip\PrivateAndReservedIpV4AndV6Validator;
use MaxMind\Db\Reader;

class GeoLocation
{
    /**
     * @var GeoCityRepository
     */
    private $geoCityRepository;

    /**
     * @var Reader
     */
    private $cityIpReader;

    /**
     * @var Reader
     */
    private $asnIpReader;

    public function __construct(
        GeoCityRepository $geoCityRepository,
        Reader $cityIpReader,
        Reader $asnIpReader
    ) {
        $this->geoCityRepository = $geoCityRepository;
        $this->cityIpReader = $cityIpReader;
        $this->asnIpReader = $asnIpReader;
    }

    /**
     * @return float|null (null is for ips not in database e.g. local ips when testing, subnet ips, databases not in maxmind db)
     *
     * @throws GeoDatabaseCrash
     */
    public function getLatitudeFromIp(string $ip): ?float
    {
        try {
            $content = $this->cityIpReader->get($ip);

            if (
                is_array($content) &&
                array_key_exists('location', $content) &&
                is_array($content['location']) &&
                array_key_exists('latitude', $content['location']) &&
                is_numeric($content['location']['latitude'])
            ) {
                return floatval($content['location']['latitude']);
            }

            if (IpV4AndV6Validator::isValid($ip)) {
                return null;
            }

            throw new \InvalidArgumentException('Could not find latitude for IP '.$ip);
        } catch (\Throwable $exception) {
            throw new GeoDatabaseCrash($exception);
        }
    }

    /**
     * @return float|null (null is for ips not in database e.g. local ips when testing, subnet ips, databases not in maxmind db)
     *
     * @throws GeoDatabaseCrash
     */
    public function getLongitudeFromIp(string $ip): ?float
    {
        if (PrivateAndReservedIpV4AndV6Validator::isValid($ip)) {
            return null;
        }

        try {
            $content = $this->cityIpReader->get($ip);

            if (
                is_array($content) &&
                array_key_exists('location', $content) &&
                is_array($content['location']) &&
                array_key_exists('longitude', $content['location']) &&
                is_numeric($content['location']['longitude'])
            ) {
                return floatval($content['location']['longitude']);
            }

            if (IpV4AndV6Validator::isValid($ip)) {
                return null;
            }

            throw new \InvalidArgumentException('Could not find longitude for IP '.$ip);
        } catch (\Throwable $exception) {
            throw new GeoDatabaseCrash($exception);
        }
    }

    /**
     * @return string|null (null is for ips not in database e.g. local ips when testing, subnet ips, databases not in maxmind db)
     *
     * @throws GeoDatabaseCrash
     */
    public function getCityFromIp(string $ip): ?string
    {
        if (
            null === $this->getLatitudeFromIp($ip) ||
            null === $this->getLongitudeFromIp($ip)
        ) {
            return null;
        }

        return $this->getCityFromLatitudeAndLongitude(
            $this->getLatitudeFromIp($ip),
            $this->getLongitudeFromIp($ip)
        );
    }

    /**
     * @param string $ip
     *
     * @return float|null (null is for ips not in database e.g. local ips when testing, subnet ips, databases not in maxmind db)
     *
     * @throws GeoDatabaseCrash
     */
    public function getCityLatitudeFromIp($ip): ?float
    {
        if (
            null === $this->getLatitudeFromIp($ip) ||
            null === $this->getLongitudeFromIp($ip)
        ) {
            return null;
        }

        return $this->getCityLatitudeFromLatitudeAndLongitude(
            $this->getLatitudeFromIp($ip),
            $this->getLongitudeFromIp($ip)
        );
    }

    /**
     * @param string $ip
     *
     * @return float|null (null is for ips not in database e.g. local ips when testing, subnet ips, databases not in maxmind db)
     *
     * @throws GeoDatabaseCrash
     */
    public function getCityLongitudeFromIp($ip): ?float
    {
        if (
            null === $this->getLatitudeFromIp($ip) ||
            null === $this->getLongitudeFromIp($ip)
        ) {
            return null;
        }

        return $this->getCityLongitudeFromLatitudeAndLongitude(
            $this->getLatitudeFromIp($ip),
            $this->getLongitudeFromIp($ip)
        );
    }

    /**
     * @return string|null (null is for ips not in database e.g. local ips when testing, subnet ips, databases not in maxmind db)
     *
     * @throws GeoDatabaseCrash
     */
    public function getCountryFromIp(string $ip): ?string
    {
        if (
            null === $this->getLatitudeFromIp($ip) ||
            null === $this->getLongitudeFromIp($ip)
        ) {
            return null;
        }

        return $this->getCountryFromLatitudeAndLongitude(
            $this->getLatitudeFromIp($ip),
            $this->getLongitudeFromIp($ip)
        );
    }

    /**
     * @return int|null (null is for ips not in database e.g. local ips when testing, subnet ips, databases not in maxmind db)
     *
     * @throws GeoDatabaseCrash
     */
    public function getAutonomousSystemNumberFromIp(string $ip): ?int
    {
        if (PrivateAndReservedIpV4AndV6Validator::isValid($ip)) {
            return null;
        }

        try {
            $content = $this->asnIpReader->get($ip);

            if (
                is_array($content) &&
                array_key_exists('autonomous_system_number', $content) &&
                is_numeric($content['autonomous_system_number'])
            ) {
                return intval($content['autonomous_system_number']);
            }

            if (IpV4AndV6Validator::isValid($ip)) {
                return null;
            }

            throw new \InvalidArgumentException('Could not find ASN for IP '.$ip);
        } catch (\Throwable $exception) {
            throw new GeoDatabaseCrash($exception);
        }
    }

    /**
     * @return string|null (null is for ips not in database e.g. local ips when testing, subnet ips, databases not in maxmind db)
     *
     * @throws GeoDatabaseCrash
     */
    public function getAutonomousSystemOrganizationFromIp(string $ip): ?string
    {
        if (PrivateAndReservedIpV4AndV6Validator::isValid($ip)) {
            return null;
        }

        try {
            $content = $this->asnIpReader->get($ip);

            if (
                is_array($content) &&
                array_key_exists('autonomous_system_number', $content) &&
                is_string($content['autonomous_system_organization'])
            ) {
                return $content['autonomous_system_organization'];
            }

            if (IpV4AndV6Validator::isValid($ip)) {
                return null;
            }

            throw new \InvalidArgumentException('Could not find ASO for IP '.$ip);
        } catch (\Throwable $exception) {
            throw new GeoDatabaseCrash($exception);
        }
    }

    /**
     * @throws GeoDatabaseCrash
     */
    public function getCityFromLatitudeAndLongitude(float $latitude, float $longitude): string
    {
        $city = $this->geoCityRepository->findNearestCityFromCoordinates($latitude, $longitude);

        return $city->getCityName();
    }

    /**
     * @throws GeoDatabaseCrash
     */
    public function getCityLatitudeFromLatitudeAndLongitude(float $latitude, float $longitude): float
    {
        $city = $this->geoCityRepository->findNearestCityFromCoordinates($latitude, $longitude);

        return $city->getCoordinates()->getLatitude();
    }

    /**
     * @throws GeoDatabaseCrash
     */
    public function getCityLongitudeFromLatitudeAndLongitude(float $latitude, float $longitude): float
    {
        $city = $this->geoCityRepository->findNearestCityFromCoordinates($latitude, $longitude);

        return $city->getCoordinates()->getLongitude();
    }

    /**
     * @throws GeoDatabaseCrash
     */
    public function getCountryFromLatitudeAndLongitude(float $latitude, float $longitude): string
    {
        $city = $this->geoCityRepository->findNearestCityFromCoordinates($latitude, $longitude);

        return $city->getCountryName();
    }
}
