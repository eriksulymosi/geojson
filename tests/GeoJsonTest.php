<?php

declare(strict_types=1);

use GeoJson\BoundingBox;
use GeoJson\Exception\UnserializationException;
use GeoJson\GeoJson;
use GeoJson\GeoJsonType;
use GeoJson\Geometry\Point;
use GeoJson\JsonUnserializable;

test('is json serializable')
    ->expect(fn () => mock(GeoJson::class))
    ->toBeInstanceOf(JsonSerializable::class);

test('is json unserializable')
    ->expect(fn () => mock(GeoJson::class))
    ->toBeInstanceOf(JsonUnserializable::class);

test('unserialization with bounding box', function ($assoc): void {
    $json = <<<'JSON'
            {
                "type": "Point",
                "coordinates": [1, 1],
                "bbox": [-180.0, -90.0, 180.0, 90.0]
            }
        JSON;

    $json = json_decode($json, $assoc);

    /** @var Point */
    $point = GeoJson::jsonUnserialize($json);

    expect($point)->toBeInstanceOf(Point::class);
    expect(GeoJsonType::from($point->getType()))->toBe(GeoJsonType::POINT);
    expect($point->getCoordinates())->toBe([1, 1]);

    $boundingBox = $point->getBoundingBox();

    expect($boundingBox)->toBeInstanceOf(BoundingBox::class);
    expect($boundingBox->getBounds())->toBe([-180.0, -90.0, 180.0, 90.0]);
})
    ->with([
        'assoc=true' => [true],
        'assoc=false' => [false],
    ])
    ->group('functional');

test('unserialization with unknown type')
    ->throws(ValueError::class)
    ->expect(fn () => GeoJson::jsonUnserialize(['type' => 'Unknown']));

test('unserialization with missing type', function (): void {
    expect(fn () => GeoJson::jsonUnserialize([]))
        ->toThrow(UnserializationException::class, 'GeoJson expected "type" property of type string, none given');
});

test(
    'unserialization with missing coordinates',
    function (GeoJsonType $type): void {
        expect(fn () => GeoJson::jsonUnserialize([
            'type' => $type->value,
        ]))->toThrow(UnserializationException::class, "{$type->value} expected \"coordinates\" property of type array, none given");
    }
)
    ->with([
        GeoJsonType::LINE_STRING->value => [GeoJsonType::LINE_STRING],
        GeoJsonType::MULTI_LINE_STRING->value => [GeoJsonType::MULTI_LINE_STRING],
        GeoJsonType::MULTI_POINT->value => [GeoJsonType::MULTI_POINT],
        GeoJsonType::MULTI_POLYGON->value => [GeoJsonType::MULTI_POLYGON],
        GeoJsonType::POINT->value => [GeoJsonType::POINT],
        GeoJsonType::POLYGON->value => [GeoJsonType::POLYGON],
    ]);

test('unserialization with invalid coordinates', function ($value): void {
    $valueType = is_object($value) ? get_class($value) : gettype($value);

    expect(fn () => GeoJson::jsonUnserialize([
        'type' => GeoJsonType::POINT->value,
        'coordinates' => $value,
    ]))
        ->toThrow(UnserializationException::class, "Point expected \"coordinates\" property of type array, {$valueType} given");
})
    ->with([
        'string' => ['1,1'],
        'int' => [1],
        'bool' => [false],
    ]);

test('feature unserialization with invalid geometry', function (): void {
    expect(fn () => GeoJson::jsonUnserialize([
        'type' => GeoJsonType::FEATURE->value,
        'geometry' => 'must be array or object, but this is a string',
    ]))
        ->toThrow(UnserializationException::class, 'Feature expected "geometry" property of type array or object, string given');
});

test('feature unserialization with invalid properties', function (): void {
    expect(fn () => GeoJson::jsonUnserialize([
        'type' => GeoJsonType::FEATURE->value,
        'properties' => 'must be array or object, but this is a string',
    ]))
        ->toThrow(UnserializationException::class, 'Feature expected "properties" property of type array or object, string given');
});
