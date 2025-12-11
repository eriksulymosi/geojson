<?php
declare(strict_types=1);

namespace GeoJson;

use ArrayObject;
use GeoJson\CoordinateReferenceSystem\CoordinateReferenceSystem;
use GeoJson\Exception\UnserializationException;
use JsonSerializable;

use function array_map;
use function is_array;
use function is_object;
use function sprintf;
use function strncmp;

/**
 * Base GeoJson object.
 *
 * @see http://www.geojson.org/geojson-spec.html#geojson-objects
 * @since 1.0
 */
abstract class GeoJson implements JsonSerializable, JsonUnserializable
{
    protected ?BoundingBox $boundingBox = null;

    protected GeoJsonType $type;

    /**
     * Return the BoundingBox for this GeoJson object.
     */
    public function getBoundingBox(): ?BoundingBox
    {
        return $this->boundingBox;
    }

    /**
     * Return the type for this GeoJson object.
     */
    public function getType(): string
    {
        return $this->type->value;
    }

    public function jsonSerialize(): array
    {
        $json = ['type' => $this->getType()];

        if (isset($this->boundingBox)) {
            $json['bbox'] = $this->boundingBox->jsonSerialize();
        }

        return $json;
    }

    final public static function jsonUnserialize(array|object $json): self
    {
        if (! is_array($json) && ! is_object($json)) {
            throw UnserializationException::invalidValue('GeoJson', $json, 'array or object');
        }

        $json = new ArrayObject((array) $json);

        if (! $json->offsetExists('type')) {
            throw UnserializationException::missingProperty('GeoJson', 'type', 'string');
        }

        $type = GeoJsonType::from($json['type']);
        $args = [];

        switch ($type) {
            case GeoJsonType::LINE_STRING:
            case GeoJsonType::MULTI_LINE_STRING:
            case GeoJsonType::MULTI_POINT:
            case GeoJsonType::MULTI_POLYGON:
            case GeoJsonType::POINT:
            case GeoJsonType::POLYGON:
                if (! $json->offsetExists('coordinates')) {
                    throw UnserializationException::missingProperty($type->value, 'coordinates', 'array');
                }

                if (! is_array($json['coordinates'])) {
                    throw UnserializationException::invalidProperty($type->value, 'coordinates', $json['coordinates'], 'array');
                }

                $args[] = $json['coordinates'];
                break;

            case GeoJsonType::FEATURE:
                $geometry = $json['geometry'] ?? null;
                $properties = $json['properties'] ?? null;
                $id = $json['id'] ?? null;

                if ($geometry !== null && ! is_array($geometry) && ! is_object($geometry)) {
                    throw UnserializationException::invalidProperty($type->value, 'geometry', $geometry, 'array or object');
                }

                if ($properties !== null && ! is_array($properties) && ! is_object($properties)) {
                    throw UnserializationException::invalidProperty($type->value, 'properties', $properties, 'array or object');
                }

                // TODO: Validate non-null $id as int or string in 2.0

                $args[] = $geometry !== null ? self::jsonUnserialize($geometry) : null;
                $args[] = $properties !== null ? (array) $properties : null;
                $args[] = $id;
                break;

            case GeoJsonType::FEATURE_COLLECTION:
                if (! $json->offsetExists('features')) {
                    throw UnserializationException::missingProperty($type->value, 'features', 'array');
                }

                if (! is_array($json['features'])) {
                    throw UnserializationException::invalidProperty($type->value, 'features', $json['features'], 'array');
                }

                $args[] = array_map([self::class, 'jsonUnserialize'], $json['features']);
                break;

            case GeoJsonType::GEOMETRY_COLLECTION:
                if (! $json->offsetExists('geometries')) {
                    throw UnserializationException::missingProperty($type->value, 'geometries', 'array');
                }

                if (! is_array($json['geometries'])) {
                    throw UnserializationException::invalidProperty($type->value, 'geometries', $json['geometries'], 'array');
                }

                $args[] = array_map([self::class, 'jsonUnserialize'], $json['geometries']);
                break;

            default:
                throw UnserializationException::unsupportedType('GeoJson', $json['type']);
        }

        if (isset($json['bbox'])) {
            $args[] = BoundingBox::jsonUnserialize($json['bbox']);
        }

        $class = sprintf('GeoJson\%s\%s', strncmp('Feature', $type->value, 7) === 0 ? 'Feature' : 'Geometry', $type->value);

        return new $class(... $args);
    }

    /**
     * Set optional BoundingBox arguments passed to a constructor.
     *
     * @todo Decide if multiple BoundingBox instances should override a
     *       previous value or be ignored
     */
    protected function setOptionalConstructorArgs(array $args): void
    {
        foreach ($args as $arg) {
            if ($arg instanceof BoundingBox) {
                $this->boundingBox = $arg;
            }
        }
    }
}
