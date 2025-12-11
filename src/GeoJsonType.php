<?php
declare(strict_types=1);

namespace GeoJson;

use Stringable;

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
}