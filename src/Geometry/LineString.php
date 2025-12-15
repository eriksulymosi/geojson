<?php

declare(strict_types=1);

namespace GeoJson\Geometry;

use GeoJson\BoundingBox;
use GeoJson\Exception\InvalidArgumentException;
use GeoJson\GeoJsonType;

use function count;

/**
 * LineString geometry object.
 *
 * Coordinates consist of an array of at least two positions.
 *
 * @see http://www.geojson.org/geojson-spec.html#linestring
 * @since 1.0
 */
class LineString extends MultiPoint
{
    protected GeoJsonType $type = GeoJsonType::LINE_STRING;

    /**
     * @param array<array<float|int>|Point> $positions
     * @param BoundingBox                   $args
     */
    public function __construct(array $positions, ...$args)
    {
        if (count($positions) < 2) {
            throw new InvalidArgumentException('LineString requires at least two positions');
        }

        parent::__construct($positions, ...$args);
    }
}
