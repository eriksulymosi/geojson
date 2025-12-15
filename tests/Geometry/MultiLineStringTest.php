<?php

declare(strict_types=1);

use GeoJson\GeoJson;
use GeoJson\GeoJsonType;
use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiLineString;

test('is subclass of geometry')
    ->expect(is_subclass_of(MultiLineString::class, Geometry::class))
    ->toBeTrue();

test('construction from line string objects')
    ->expect(
        new MultiLineString([
            new LineString([[1, 1], [2, 2]]),
            new LineString([[3, 3], [4, 4]]),
        ])->getCoordinates()
    )
    ->toBe(
        new MultiLineString([
            [[1, 1], [2, 2]],
            [[3, 3], [4, 4]],
        ])->getCoordinates()
    );

test('serialization', function (): void {
    $coordinates = [
        [[1, 1], [2, 2]],
        [[3, 3], [4, 4]],
    ];

    $multiLineString = new MultiLineString($coordinates);

    $expected = [
        'type' => GeoJsonType::MULTI_LINE_STRING->value,
        'coordinates' => $coordinates,
    ];

    expect(GeoJsonType::from($multiLineString->getType()))->toBe(GeoJsonType::MULTI_LINE_STRING);
    expect($multiLineString->getCoordinates())->toBe($coordinates);
    expect($multiLineString->jsonSerialize())->toBe($expected);
});

test('unserialization', function ($assoc): void {
    $json = <<<'JSON'
        {
            "type": "MultiLineString",
            "coordinates": [
                [ [1, 1], [2, 2] ],
                [ [3, 3], [4, 4] ]
            ]
        }
        JSON;

    $json = json_decode($json, $assoc);

    /** @var MultiLineString */
    $multiLineString = GeoJson::jsonUnserialize($json);

    $expectedCoordinates = [
        [[1, 1], [2, 2]],
        [[3, 3], [4, 4]],
    ];

    expect($multiLineString)->toBeInstanceOf(MultiLineString::class);
    expect(GeoJsonType::from($multiLineString->getType()))->toBe(GeoJsonType::MULTI_LINE_STRING);
    expect($multiLineString->getCoordinates())->toBe($expectedCoordinates);
})
    ->with([
        'assoc=true' => [true],
        'assoc=false' => [false],
    ])
    ->group('functional');
