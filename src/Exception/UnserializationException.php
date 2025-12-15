<?php

declare(strict_types=1);

namespace GeoJson\Exception;

use GeoJson\GeoJsonType;
use RuntimeException;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

class UnserializationException extends RuntimeException implements Exception
{
    /**
     * Creates an UnserializationException for a property with an invalid type.
     *
     * @param mixed $value
     */
    public static function invalidProperty(GeoJsonType|string $context, string $property, $value, string $expectedType): self
    {
        return new self(sprintf(
            '%s expected "%s" property of type %s, %s given',
            $context,
            $property,
            $expectedType,
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }

    /**
     * Creates an UnserializationException for a missing property.
     */
    public static function missingProperty(GeoJsonType|string $context, string $property, string $expectedType): self
    {
        return new self(sprintf(
            '%s expected "%s" property of type %s, none given',
            $context,
            $property,
            $expectedType
        ));
    }
}
