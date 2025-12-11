<?php
declare(strict_types=1);

use GeoJson\Exception\InvalidArgumentException;
use GeoJson\GeoJson;
use GeoJson\GeoJsonType;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiPoint;

test('is subclass of multi point', function () {
    expect(is_subclass_of(LineString::class, MultiPoint::class))->toBeTrue();
});

test('constructor should require at least two positions', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('LineString requires at least two positions');

    new LineString([[1, 1]]);
});

test('serialization', function () {
    $coordinates = [[1, 1], [2, 2]];
    $lineString = new LineString($coordinates);

    $expected = [
        'type' => GeoJsonType::LINE_STRING->value,
        'coordinates' => $coordinates,
    ];

    expect(GeoJsonType::from($lineString->getType()))->toBe(GeoJsonType::LINE_STRING);
    expect($lineString->getCoordinates())->toBe($coordinates);
    expect($lineString->jsonSerialize())->toBe($expected);
});

test('unserialization', function ($assoc) {
    $json = <<<'JSON'
    {
        "type": "LineString",
        "coordinates": [
            [1, 1],
            [2, 2]
        ]
    }
    JSON;

    $json = json_decode($json, $assoc);
    
    /** @var LineString */
    $lineString = GeoJson::jsonUnserialize($json);

    $expectedCoordinates = [[1, 1], [2, 2]];

    expect($lineString)->toBeInstanceOf(LineString::class);
    expect(GeoJsonType::from($lineString->getType()))->toBe(GeoJsonType::LINE_STRING);
    expect($lineString->getCoordinates())->toBe($expectedCoordinates);
})
    ->with([
        'assoc=true' => [true],
        'assoc=false' => [false],
    ])
    ->group('functional');
