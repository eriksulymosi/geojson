<?php

declare(strict_types=1);

namespace GeoJson;

use function sprintf;

enum GeoJsonType: string
{
    case LINE_STRING = 'LineString';
    case MULTI_LINE_STRING = 'MultiLineString';
    case MULTI_POINT = 'MultiPoint';
    case MULTI_POLYGON = 'MultiPolygon';
    case POINT = 'Point';
    case POLYGON = 'Polygon';
    case FEATURE = 'Feature';
    case FEATURE_COLLECTION = 'FeatureCollection';
    case GEOMETRY_COLLECTION = 'GeometryCollection';

    public function getTypedClassFullName(): string
    {
        $classNamespace = match ($this) {
            self::FEATURE,
            self::FEATURE_COLLECTION => 'Feature',
            default => 'Geometry'
        };

        return sprintf('GeoJson\%s\%s', $classNamespace, $this->value);
    }
}
