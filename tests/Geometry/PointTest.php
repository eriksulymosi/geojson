<?php
declare(strict_types=1);

use GeoJson\Exception\InvalidArgumentException;
use GeoJson\GeoJson;
use GeoJson\GeoJsonType;
use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\Point;

test('is subclass of geometry')
    ->expect(is_subclass_of(Point::class, Geometry::class))
    ->toBeTrue();

test('constructor should require at least two elements in position', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Position requires at least two elements');

    new Point([1]);
});

test('constructor should require integer or float elements in position', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Position elements must be integers or floats');

    new Point(func_get_args());
})
    ->with([
        'strings' => ['1.0', '2'],
        'objects' => [new stdClass(), new stdClass()],
        'arrays' => [[], []],
    ]);

test('constructor should allow more than two elements in aposition', function () {
    $point = new Point([1, 2, 3, 4]);

    expect($point->getCoordinates())->toEqual([1, 2, 3, 4]);
});

test('serialization', function () {
    $coordinates = [1, 1];
    $point = new Point($coordinates);

    $expected = [
        'type' => GeoJsonType::POINT->value,
        'coordinates' => $coordinates,
    ];

    expect(GeoJsonType::from($point->getType()))->toBe(GeoJsonType::POINT);
    expect($point->getCoordinates())->toBe($coordinates);
    expect($point->jsonSerialize())->toBe($expected);
});

test('unserialization', function ($assoc) {
    $json = <<<'JSON'
    {
        "type": "Point",
        "coordinates": [1, 1]
    }
    JSON;

    $json = json_decode($json, $assoc);
    
    /** @var Point */
    $point = GeoJson::jsonUnserialize($json);

    expect($point)->toBeInstanceOf(Point::class);
    expect(GeoJsonType::from($point->getType()))->toBe(GeoJsonType::POINT);
    expect($point->getCoordinates())->toBe([1, 1]);
})
    ->with([
        'assoc=true' => [true],
        'assoc=false' => [false],
    ])
    ->group('functional');