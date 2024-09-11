<?php

declare(strict_types=1);

namespace  Galeas\Api\Service\GeoLocation;

class Coordinates
{
    /**
     * @var float
     */
    private $longitude;

    /**
     * @var float
     */
    private $latitude;

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }
}
