<?php

declare(strict_types=1);

use GeoJson\Exception\InvalidArgumentException;
use GeoJson\Exception\UnserializationException;
use GeoJson\GeoJson;
use GeoJson\GeoJsonType;
use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\GeometryCollection;
use GeoJson\Geometry\Point;

test('is subclass of geometry')
    ->expect(is_subclass_of(GeometryCollection::class, Geometry::class))
    ->toBeTrue();

test('constructor should require array of geometry objects')
    ->throws(InvalidArgumentException::class, 'GeometryCollection may only contain Geometry objects')
    ->expect(fn () => new GeometryCollection([new stdClass()]));

test('constructor should reindex geometries array numerically', function (): void {
    $geometry1 = mock(Geometry::class);
    $geometry2 = mock(Geometry::class);

    $geometries = [
        'one' => $geometry1,
        'two' => $geometry2,
    ];

    $collection = new GeometryCollection($geometries);

    expect(iterator_to_array($collection))->toBe([$geometry1, $geometry2]);
});

test('is traversable', function (): void {
    $geometries = [
        mock(Geometry::class),
        mock(Geometry::class),
    ];

    $collection = new GeometryCollection($geometries);

    expect($collection)->toBeInstanceOf('Traversable');
    expect(iterator_to_array($collection))->toBe($geometries);
});

test('is countable', function (): void {
    $geometries = [
        mock(Geometry::class),
        mock(Geometry::class),
    ];

    $collection = new GeometryCollection($geometries);

    expect($collection)->toBeInstanceOf('Countable');
    expect($collection)->toHaveCount(2);
});

test('serialization', function (): void {
    $geometries = [
        mock(Geometry::class),
        mock(Geometry::class),
    ];

    $geometries[0]->shouldReceive('jsonSerialize')->andReturn(['geometry1']);
    $geometries[1]->shouldReceive('jsonSerialize')->andReturn(['geometry2']);

    $collection = new GeometryCollection($geometries);

    $expected = [
        'type' => GeoJsonType::GEOMETRY_COLLECTION->value,
        'geometries' => [['geometry1'], ['geometry2']],
    ];

    expect(GeoJsonType::from($collection->getType()))->toBe(GeoJsonType::GEOMETRY_COLLECTION);
    expect($collection->getGeometries())->toBe($geometries);
    expect($collection->jsonSerialize())->toBe($expected);
});

test('unserialization', function ($assoc): void {
    $json = <<<'JSON'
        {
            "type": "GeometryCollection",
            "geometries": [
                {
                    "type": "Point",
                    "coordinates": [1, 1]
                }
            ]
        }
        JSON;

    $json = json_decode($json, $assoc);

    /** @var GeometryCollection */
    $collection = GeoJson::jsonUnserialize($json);

    expect($collection)->toBeInstanceOf(GeometryCollection::class);
    expect(GeoJsonType::from($collection->getType()))->toBe(GeoJsonType::GEOMETRY_COLLECTION);
    expect($collection)->toHaveCount(1);

    $geometries = iterator_to_array($collection);
    $geometry = $geometries[0];

    expect($geometry)->toBeInstanceOf(Point::class);
    expect(GeoJsonType::from($geometry->getType()))->toBe(GeoJsonType::POINT);
    expect($geometry->getCoordinates())->toBe([1, 1]);
})
    ->with([
        'assoc=true' => [true],
        'assoc=false' => [false],
    ])
    ->group('functional');

test('unserialization should require geometries property')
    ->throws(UnserializationException::class, 'GeometryCollection expected "geometries" property of type array, none given')
    ->expect(fn () => GeoJson::jsonUnserialize(['type' => GeoJsonType::GEOMETRY_COLLECTION->value]));

test('unserialization should require geometries array')
    ->throws(UnserializationException::class, 'GeometryCollection expected "geometries" property of type array')
    ->expect(fn () => GeoJson::jsonUnserialize(['type' => GeoJsonType::GEOMETRY_COLLECTION->value, 'geometries' => null]));
