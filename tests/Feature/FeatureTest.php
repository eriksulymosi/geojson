<?php
declare(strict_types=1);

use GeoJson\Feature\Feature;
use GeoJson\GeoJson;
use GeoJson\GeoJsonType;
use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\Point;

test('is subclass of geo json')
    ->expect(is_subclass_of(Feature::class, GeoJson::class))
    ->toBeTrue();

test('serialization', function () {
    $geometry = mock(Geometry::class);

    $geometry->shouldReceive('jsonSerialize')->andReturn(['geometry']);

    $properties = ['key' => 'value'];
    $id = 'identifier';

    $feature = new Feature($geometry, $properties, $id);

    $expected = [
        'type' => GeoJsonType::FEATURE->value,
        'geometry' => ['geometry'],
        'properties' => $properties,
        'id' => 'identifier',
    ];

    expect(GeoJsonType::from($feature->getType()))->toBe(GeoJsonType::FEATURE);
    expect($feature->getGeometry())->toBe($geometry);
    expect($feature->getId())->toBe($id);
    expect($feature->getProperties())->toBe($properties);
    expect($feature->jsonSerialize())->toBe($expected);
});

test('serialization with null constructor arguments', function () {
    $feature = new Feature();

    $expected = [
        'type' => GeoJsonType::FEATURE->value,
        'geometry' => null,
        'properties' => null,
    ];

    expect($feature->jsonSerialize())->toBe($expected);
});

test('serialization should convert empty properties array to object', function () {
    $feature = new Feature(null, []);

    $expected = [
        'type' => GeoJsonType::FEATURE->value,
        'geometry' => null,
        'properties' => new stdClass(),
    ];

    expect($feature->jsonSerialize())->toEqual($expected);
});

test('unserialization', function ($assoc) {
    $json = <<<'JSON'
    {
        "type": "Feature",
        "id": "test.feature.1",
        "properties": {
            "key": "value"
        },
        "geometry": {
            "type": "Point",
            "coordinates": [1, 1]
        }
    }
    JSON;

    $json = json_decode($json, $assoc);
    
    /** @var Feature */
    $feature = GeoJson::jsonUnserialize($json);

    expect($feature)->toBeInstanceOf(Feature::class);
    expect(GeoJsonType::from($feature->getType()))->toBe(GeoJsonType::FEATURE);
    expect($feature->getId())->toBe('test.feature.1');
    expect($feature->getProperties())->toBe(['key' => 'value']);

    $geometry = $feature->getGeometry();

    expect($geometry)->toBeInstanceOf(Point::class);
    expect(GeoJsonType::from($geometry->getType()))->toBe(GeoJsonType::POINT);
    expect($geometry->getCoordinates())->toBe([1, 1]);
})
    ->with([
        'assoc=true' => [true],
        'assoc=false' => [false],
    ])
    ->group('functional');

