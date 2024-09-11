<?php

declare(strict_types=1);

namespace Galeas\Api\Service\GeoLocation;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class GeoCityRepository extends DocumentRepository
{
    /**
     * @throws GeoDatabaseCrash
     */
    public function findNearestCityFromCoordinates(float $latitude, float $longitude): GeoCity
    {
        try {
            if (
                $latitude > 90 ||
                $latitude < -90
            ) {
                throw new \InvalidArgumentException('Invalid latitude '.$latitude);
            }
            if (
                $latitude > 180 ||
                $latitude < -180
            ) {
                throw new \InvalidArgumentException('Invalid longitude '.$longitude);
            }

            $city = $this->createQueryBuilder()
                ->field('cityName')->notEqual(null)
                ->field('cityName')->notEqual('')
                ->field('countryName')->notEqual(null)
                ->field('countryName')->notEqual('')
                ->refresh()
                ->field('coordinates')
                ->near($longitude, $latitude)
                ->getQuery()
                ->getSingleResult();

            if ($city instanceof GeoCity) {
                return $city;
            }

            throw new \InvalidArgumentException('Could not find city for latitude '.$latitude.' and longitude '.$longitude);
        } catch (\Throwable $exception) {
            throw new GeoDatabaseCrash($exception);
        }
    }
}
