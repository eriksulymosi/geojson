<?php

declare(strict_types=1);

use GeoJson\GeoJson;
use GeoJson\GeoJsonType;
use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\LinearRing;
use GeoJson\Geometry\Polygon;

test('is subclass of geometry')
    ->expect(is_subclass_of(Polygon::class, Geometry::class))
    ->toBeTrue();

test('construction from linear ring objects')
    ->expect(
        new Polygon([
            new LinearRing([[0, 0], [0, 4], [4, 4], [4, 0], [0, 0]]),
            new LinearRing([[1, 1], [1, 3], [3, 3], [3, 1], [1, 1]]),
        ])->getCoordinates()
    )->toBe(
        new Polygon([
            [[0, 0], [0, 4], [4, 4], [4, 0], [0, 0]],
            [[1, 1], [1, 3], [3, 3], [3, 1], [1, 1]],
        ])->getCoordinates()
    );

test('serialization', function (): void {
    $coordinates = [
        [[0, 0], [0, 4], [4, 4], [4, 0], [0, 0]],
        [[1, 1], [1, 3], [3, 3], [3, 1], [1, 1]],
    ];

    $polygon = new Polygon($coordinates);

    $expected = [
        'type' => GeoJsonType::POLYGON->value,
        'coordinates' => $coordinates,
    ];

    expect(GeoJsonType::from($polygon->getType()))->toBe(GeoJsonType::POLYGON);
    expect($polygon->getCoordinates())->toBe($coordinates);
    expect($polygon->jsonSerialize())->toBe($expected);
});

test('unserialization', function ($assoc): void {
    $json = <<<'JSON'
        {
            "type": "Polygon",
            "coordinates": [
                [ [0, 0], [0, 4], [4, 4], [4, 0], [0, 0] ],
                [ [1, 1], [1, 3], [3, 3], [3, 1], [1, 1] ]
            ]
        }
        JSON;

    $json = json_decode($json, $assoc);

    /** @var Polygon */
    $polygon = GeoJson::jsonUnserialize($json);

    $expectedCoordinates = [
        [[0, 0], [0, 4], [4, 4], [4, 0], [0, 0]],
        [[1, 1], [1, 3], [3, 3], [3, 1], [1, 1]],
    ];

    expect($polygon)->toBeInstanceOf(Polygon::class);
    expect(GeoJsonType::from($polygon->getType()))->toBe(GeoJsonType::POLYGON);
    expect($polygon->getCoordinates())->toBe($expectedCoordinates);
})
    ->with([
        'assoc=true' => [true],
        'assoc=false' => [false],
    ])
    ->group('functional');
