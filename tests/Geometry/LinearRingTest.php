<?php
declare(strict_types=1);

use GeoJson\Exception\InvalidArgumentException;
use GeoJson\GeoJsonType;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\LinearRing;
use GeoJson\Geometry\Point;

test('is subclass of line string')
    ->expect(is_subclass_of(LinearRing::class, LineString::class))
    ->toBeTrue();

test('constructor should require at least four positions', function () {
    new LinearRing([
        [1, 1],
        [2, 2],
        [3, 3],
    ]);
})
    ->throws(InvalidArgumentException::class, 'LinearRing requires at least four positions');

test('constructor should require equivalent first and last positions', function () {
    new LinearRing([
        [1, 1],
        [2, 2],
        [3, 3],
        [4, 4],
    ]);
})
    ->throws(InvalidArgumentException::class, 'LinearRing requires the first and last positions to be equivalent');

test('constructor should accept equivalent point objects and position arrays', function () {
    $linearRing1 = new LinearRing([
        new Point([1, 1]),
        new Point([2, 2]),
        new Point([3, 3]),
        new Point([1, 1]),
    ]);

    $linearRing2 = new LinearRing([
        [1, 1],
        [2, 2],
        [3, 3],
        [1, 1],
    ]);

    expect($linearRing1->getCoordinates())->toBe($linearRing2->getCoordinates());
});

test('serialization', function () {
    $coordinates = [[1, 1], [2, 2], [3, 3], [1, 1]];
    $linearRing = new LinearRing($coordinates);

    $expected = [
        'type' => GeoJsonType::LINE_STRING->value,
        'coordinates' => $coordinates,
    ];

    expect(GeoJsonType::from($linearRing->getType()))->toBe(GeoJsonType::LINE_STRING);
    expect($linearRing->getCoordinates())->toBe($coordinates);
    expect($linearRing->jsonSerialize())->toBe($expected);
});