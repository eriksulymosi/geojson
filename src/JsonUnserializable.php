<?php

declare(strict_types=1);

namespace GeoJson;

use GeoJson\Exception\UnserializationException;

/**
 * JsonUnserializable interface for creating an object from decoded JSON.
 *
 * This is used as a factory method for GeoJson, BoundingBox classes.
 *
 * @since 1.0
 */
interface JsonUnserializable
{
    /**
     * Factory method for creating an object from a decoded JSON value.
     *
     * @throws UnserializationException
     */
    public static function jsonUnserialize(array $json): self;
}
