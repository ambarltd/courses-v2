<?php

declare(strict_types=1);

namespace Galeas\Api\Service\GeoLocation;

class GeoCity
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $geoNameId;

    /**
     * @var string
     */
    private $localeCode;

    /**
     * @var string
     */
    private $countryName;

    /**
     * @var string
     */
    private $cityName;

    /**
     * @var Coordinates
     */
    public $coordinates;

    public function getId(): string
    {
        return $this->id;
    }

    public function getGeoNameId(): int
    {
        return $this->geoNameId;
    }

    public function getLocaleCode(): string
    {
        return $this->localeCode;
    }

    public function getCountryName(): string
    {
        return $this->countryName;
    }

    public function getCityName(): string
    {
        return $this->cityName;
    }

    public function getCoordinates(): Coordinates
    {
        return $this->coordinates;
    }
}
