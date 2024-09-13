<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event;

use Galeas\Api\Common\Event\Exception\ArraysNotAllowedWhenMappingPayload;
use Galeas\Api\Common\Event\Exception\JsonEventEncodingError;
use Galeas\Api\Common\Event\Exception\PropertyIsOfInvalidType;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;

/**
 * Note that \DateTimeImmutable objects in the payload (main payload or metadata) are saved with timezone.
 * This is because these dates might come from users, and the data about the timezone they sent it on, could be valuable.
 * It's up to projections to figure out how they deal with sorting among different timezones.
 */
abstract class PayloadMapper
{
    /**
     * @throws InvalidId
     */
    private static function serializedArrayPayloadToArrayPayload(array $serializedArrayPayload): array
    {
        $payload = [];

        foreach ($serializedArrayPayload as $propertyName => $value) {
            if (
                is_array($value) &&
                array_key_exists('type', $value) &&
                'galeas_datetime' === $value['type']
            ) {
                $value = \DateTimeImmutable::createFromFormat(
                    'Y-m-d H:i:s.u',
                    $value['datetime'],
                    new \DateTimeZone($value['timezone'])
                );
            }
            if (
                is_array($value) &&
                array_key_exists('type', $value) &&
                'galeas_id' === $value['type']
            ) {
                $value = Id::fromId(
                    $value['id']
                );
            }

            if (
                is_array($value) &&
                (!array_key_exists('type', $value))
            ) {
                $value = self::serializedArrayPayloadToArrayPayload($value);
            }

            $payload[$propertyName] = $value;
        }

        return $payload;
    }

    /**
     * @throws InvalidId
     */
    public static function jsonPayloadToArrayPayload(string $jsonPayload): array
    {
        return self::serializedArrayPayloadToArrayPayload(
            json_decode(
                $jsonPayload,
                true
            )
        );
    }

    /**
     * @throws ArraysNotAllowedWhenMappingPayload|PropertyIsOfInvalidType
     */
    private static function arrayPayloadToSerializedArrayPayload(array $arrayPayload, bool $arrayPropertiesAllowed): array
    {
        $payload = [];

        foreach ($arrayPayload as $propertyName => $value) {
            if (
                (!is_array($value)) &&
                (!is_string($value)) &&
                (!is_null($value)) &&
                (!is_bool($value)) &&
                (!is_int($value)) &&
                (!is_float($value)) &&
                (!($value instanceof Id)) &&
                (!($value instanceof \DateTimeImmutable))
            ) {
                throw new PropertyIsOfInvalidType(sprintf('Property %s is a %s, instead it should be one of: string, null, boolean, integer, float, \DateTimeImmutable, ..\Id\Id', $propertyName, gettype($value)));
            }

            if (
                is_array($value) &&
                $arrayPropertiesAllowed
            ) {
                $value = self::arrayPayloadToSerializedArrayPayload($value, $arrayPropertiesAllowed);
            }

            if (
                is_array($value) &&
                (!$arrayPropertiesAllowed)
            ) {
                throw new ArraysNotAllowedWhenMappingPayload();
            }

            if ($value instanceof \DateTimeImmutable) {
                $value = [
                    'type' => 'galeas_datetime',
                    'datetime' => $value->format('Y-m-d H:i:s.u'),
                    'timezone' => $value->getTimezone()->getName(),
                ];
            }

            if ($value instanceof Id) {
                $value = [
                    'type' => 'galeas_id',
                    'id' => $value->id(),
                ];
            }

            $payload[$propertyName] = $value;
        }

        return $payload;
    }

    /**
     * @throws JsonEventEncodingError|PropertyIsOfInvalidType|ArraysNotAllowedWhenMappingPayload
     */
    public static function arrayPayloadToJsonPayload(array $arrayPayload, bool $arrayPropertiesAllowed): string
    {
        $return = json_encode(
            self::arrayPayloadToSerializedArrayPayload(
                $arrayPayload,
                $arrayPropertiesAllowed
            )
        );

        if (is_string($return)) {
            return $return;
        }

        throw new JsonEventEncodingError('Error in arrayPayloadToJsonPayload');
    }
}
