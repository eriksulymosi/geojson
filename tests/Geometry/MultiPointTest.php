<?php
declare(strict_types=1);

use GeoJson\GeoJson;
use GeoJson\GeoJsonType;
use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\MultiPoint;
use GeoJson\Geometry\Point;

test('is subclass of geometry')
    ->expect(is_subclass_of(MultiPoint::class, Geometry::class))
    ->toBeTrue();

test('construction from point objects', function () {
    $multiPoint1 = new MultiPoint([
        new Point([1, 1]),
        new Point([2, 2]),
    ]);

    $multiPoint2 = new MultiPoint([
        [1, 1],
        [2, 2],
    ]);

    expect($multiPoint2->getCoordinates())->toBe($multiPoint1->getCoordinates());
});

test('serialization', function () {
    $coordinates = [[1, 1], [2, 2]];
    $multiPoint = new MultiPoint($coordinates);

    $expected = [
        'type' => GeoJsonType::MULTI_POINT->value,
        'coordinates' => $coordinates,
    ];

    expect(GeoJsonType::from($multiPoint->getType()))->toBe(GeoJsonType::MULTI_POINT);
    expect($multiPoint->getCoordinates())->toBe($coordinates);
    expect($multiPoint->jsonSerialize())->toBe($expected);
});

test('unserialization', function ($assoc) {
    $json = <<<'JSON'
    {
        "type": "MultiPoint",
        "coordinates": [
            [1, 1],
            [2, 2]
        ]
    }
    JSON;

    $json = json_decode($json, $assoc);
    
    /** @var MultiPoint */
    $multiPoint = GeoJson::jsonUnserialize($json);

    $expectedCoordinates = [[1, 1], [2, 2]];

    expect($multiPoint)->toBeInstanceOf(MultiPoint::class);
    expect(GeoJsonType::from($multiPoint->getType()))->toBe(GeoJsonType::MULTI_POINT);
    expect($multiPoint->getCoordinates())->toBe($expectedCoordinates);
})
    ->with([
        'assoc=true' => [true],
        'assoc=false' => [false],
    ])
    ->group('functional');