<?php
declare(strict_types=1);

use GeoJson\GeoJson;
use GeoJson\GeoJsonType;
use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Polygon;

test('is subclass of geometry', function () {
    expect(is_subclass_of(MultiPolygon::class, Geometry::class))->toBeTrue();
});

test('construction from polygon objects', function () {
    $multiPolygon1 = new MultiPolygon([
        new Polygon([[[0, 0], [0, 4], [4, 4], [4, 0], [0, 0]]]),
        new Polygon([[[1, 1], [1, 3], [3, 3], [3, 1], [1, 1]]]),
    ]);

    $multiPolygon2 = new MultiPolygon([
        [[[0, 0], [0, 4], [4, 4], [4, 0], [0, 0]]],
        [[[1, 1], [1, 3], [3, 3], [3, 1], [1, 1]]],
    ]);

    expect($multiPolygon2->getCoordinates())->toBe($multiPolygon1->getCoordinates());
});

test('serialization', function () {
    $coordinates = [
        [[[0, 0], [0, 4], [4, 4], [4, 0], [0, 0]]],
        [[[1, 1], [1, 3], [3, 3], [3, 1], [1, 1]]],
    ];

    $multiPolygon = new MultiPolygon($coordinates);

    $expected = [
        'type' => GeoJsonType::MULTI_POLYGON->value,
        'coordinates' => $coordinates,
    ];

    expect(GeoJsonType::from($multiPolygon->getType()))->toBe(GeoJsonType::MULTI_POLYGON);
    expect($multiPolygon->getCoordinates())->toBe($coordinates);
    expect($multiPolygon->jsonSerialize())->toBe($expected);
});

test('unserialization', function ($assoc) {
    $json = <<<'JSON'
    {
        "type": "MultiPolygon",
        "coordinates": [
            [ [ [0, 0], [0, 4], [4, 4], [4, 0], [0, 0] ] ],
            [ [ [1, 1], [1, 3], [3, 3], [3, 1], [1, 1] ] ]
        ]
    }
    JSON;

    $json = json_decode($json, $assoc);
    
    /** @var MultiPolygon */
    $multiPolygon = GeoJson::jsonUnserialize($json);

    $expectedCoordinates = [
        [[[0, 0], [0, 4], [4, 4], [4, 0], [0, 0]]],
        [[[1, 1], [1, 3], [3, 3], [3, 1], [1, 1]]],
    ];

    expect($multiPolygon)->toBeInstanceOf(MultiPolygon::class);
    expect(GeoJsonType::from($multiPolygon->getType()))->toBe(GeoJsonType::MULTI_POLYGON);
    expect($multiPolygon->getCoordinates())->toBe($expectedCoordinates);
})
    ->with([
        'assoc=true' => [true],
        'assoc=false' => [false],
    ])
    ->group('functional');